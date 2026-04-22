<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Group;
use App\Models\StatementRecord;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LegacySqlImportSeeder extends Seeder
{
    private const LEGACY_GROUP_SEED_KEY = 'splitmate-legacy-group-1';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlPath = $this->resolveLegacySqlPath();
        $sql = File::get($sqlPath);

        $userRows = $this->extractInsertRows($sql, 'users');
        $expenseRows = $this->extractInsertRows($sql, 'expenses');
        $statementRows = $this->extractInsertRows($sql, 'statement_records');

        if (empty($userRows) || empty($expenseRows) || empty($statementRows)) {
            throw new \RuntimeException('Legacy SQL import aborted: missing users/expenses/statement_records INSERT data.');
        }

        $summary = DB::transaction(function () use ($userRows, $expenseRows, $statementRows) {
            $userIdMap = $this->importUsers($userRows);
            $group = $this->ensureLegacyGroup($userIdMap, $userRows);
            $this->ensureGroupMembership($group, $userIdMap, $userRows);
            [$expenseIdMap, $receiptStats] = $this->importExpenses($group, $expenseRows, $userIdMap);
            $statementCount = $this->importStatementRecords($group, $statementRows, $userIdMap, $expenseIdMap);

            return [
                'users' => count($userIdMap),
                'expenses' => count($expenseIdMap),
                'statements' => $statementCount,
                'receipt_copied' => $receiptStats['copied'],
                'receipt_missing' => $receiptStats['missing'],
            ];
        });

        $this->command?->info('Legacy SQL import complete.');
        $this->command?->info("Users imported: {$summary['users']}");
        $this->command?->info("Expenses imported: {$summary['expenses']}");
        $this->command?->info("Statement records imported: {$summary['statements']}");
        $this->command?->info("Receipt files copied: {$summary['receipt_copied']}");
        if ($summary['receipt_missing'] > 0) {
            $this->command?->warn("Receipt files missing in public/uploads/receipts: {$summary['receipt_missing']}");
        }
    }

    /**
     * @return array<int, int> old_user_id => new_user_id
     */
    private function importUsers(array $rows): array
    {
        $map = [];

        foreach ($rows as $row) {
            $oldId = (int) $row['id'];
            $name = (string) $row['name'];
            $password = (string) $row['password'];
            $isActive = (bool) ((int) $row['is_active']);
            $createdAt = $row['created_at'];
            $updatedAt = $row['updated_at'];

            $email = $this->legacyEmailFor($name, $oldId);
            $uuid = $this->deterministicUuid('user', (string) $oldId);

            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'uuid' => $uuid,
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'is_active' => $isActive,
                    'email_verified_at' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);
            } else {
                $user->fill([
                    'name' => $name,
                    'is_active' => $isActive,
                    'password' => $password,
                ]);

                if (!$user->uuid) {
                    $user->uuid = $uuid;
                }
                if (!$user->email_verified_at) {
                    $user->email_verified_at = $createdAt;
                }
                $user->save();
            }

            $map[$oldId] = $user->id;
        }

        return $map;
    }

    private function ensureLegacyGroup(array $userIdMap, array $userRows): Group
    {
        $creatorOldId = (int) $userRows[0]['id'];
        $creatorId = $userIdMap[$creatorOldId] ?? reset($userIdMap);
        if (!$creatorId) {
            throw new \RuntimeException('Legacy SQL import aborted: could not resolve group creator user.');
        }

        $groupId = $this->deterministicUuid('group', self::LEGACY_GROUP_SEED_KEY);
        $inviteCode = strtoupper(substr(hash('sha1', self::LEGACY_GROUP_SEED_KEY), 0, 8));

        return Group::updateOrCreate(
            ['id' => $groupId],
            [
                'name' => 'Legacy SplitMate Group',
                'invite_code' => $inviteCode,
                'created_by_user_id' => $creatorId,
                'currency_code' => 'USD',
                'expense_categories' => Group::defaultExpenseCategories(),
            ]
        );
    }

    /**
     * @param array<int, int> $userIdMap
     * @param array<int, array<string, mixed>> $userRows
     */
    private function ensureGroupMembership(Group $group, array $userIdMap, array $userRows): void
    {
        foreach ($userRows as $index => $row) {
            $oldId = (int) $row['id'];
            $newUserId = $userIdMap[$oldId] ?? null;
            if (!$newUserId) {
                continue;
            }

            DB::table('group_user')->updateOrInsert(
                [
                    'group_id' => $group->id,
                    'user_id' => $newUserId,
                ],
                [
                    'role' => $index === 0 ? 'admin' : 'member',
                    'is_active' => true,
                    'joined_at' => $row['created_at'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * @param array<int, int> $userIdMap
     * @return array{0: array<int, int>, 1: array{copied:int,missing:int}}
     */
    private function importExpenses(Group $group, array $rows, array $userIdMap): array
    {
        $expenseIdMap = [];
        $copiedReceipts = 0;
        $missingReceipts = 0;

        foreach ($rows as $row) {
            $oldExpenseId = (int) $row['id'];
            $uuid = $this->deterministicUuid('expense', (string) $oldExpenseId);

            $paidByOldId = (int) $row['paid_by_user_id'];
            $paidByUserId = $userIdMap[$paidByOldId] ?? null;
            if (!$paidByUserId) {
                throw new \RuntimeException("Legacy SQL import aborted: expense {$oldExpenseId} references unknown user {$paidByOldId}.");
            }

            $amount = (float) $row['amount'];
            $amountCents = (int) round($amount * 100);
            $participantsOldIds = $this->decodeLegacyParticipantIds($row['participant_ids']);
            $participantUuids = [];
            foreach ($participantsOldIds as $oldUserId) {
                $newUserId = $userIdMap[$oldUserId] ?? null;
                if (!$newUserId) {
                    continue;
                }
                $uuidValue = User::whereKey($newUserId)->value('uuid');
                if ($uuidValue) {
                    $participantUuids[] = $uuidValue;
                }
            }
            $participantUuids = array_values(array_unique($participantUuids));

            $description = (string) $row['description'];
            $receiptPhoto = $row['receipt_photo'];
            if ($receiptPhoto) {
                if ($this->copyLegacyReceiptToPublicDisk((string) $receiptPhoto)) {
                    $copiedReceipts++;
                } else {
                    $missingReceipts++;
                }
            }

            $expense = Expense::updateOrCreate(
                ['uuid' => $uuid],
                [
                    'group_id' => $group->id,
                    'title' => $description,
                    'description' => $description,
                    'amount_cents' => $amountCents,
                    'amount' => number_format($amount, 2, '.', ''),
                    'paid_by_user_id' => $paidByUserId,
                    'receipt_photo' => $receiptPhoto,
                    'expense_date' => $row['expense_date'],
                    'category' => $this->normalizeCategory($description),
                    'user_count_at_time' => $row['user_count_at_time'] ? (int) $row['user_count_at_time'] : count($participantUuids),
                    'participant_ids' => $participantUuids,
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]
            );

            $expenseIdMap[$oldExpenseId] = $expense->id;
        }

        return [$expenseIdMap, ['copied' => $copiedReceipts, 'missing' => $missingReceipts]];
    }

    /**
     * @param array<int, int> $userIdMap
     * @param array<int, int> $expenseIdMap
     */
    private function importStatementRecords(Group $group, array $rows, array $userIdMap, array $expenseIdMap): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $oldStatementId = (int) $row['id'];
            $uuid = $this->deterministicUuid('statement', (string) $oldStatementId);

            $oldUserId = (int) $row['user_id'];
            $newUserId = $userIdMap[$oldUserId] ?? null;
            if (!$newUserId) {
                throw new \RuntimeException("Legacy SQL import aborted: statement {$oldStatementId} references unknown user {$oldUserId}.");
            }

            $oldExpenseId = $row['expense_id'] !== null ? (int) $row['expense_id'] : null;
            $newExpenseId = $oldExpenseId !== null ? ($expenseIdMap[$oldExpenseId] ?? null) : null;

            $amount = (float) $row['amount'];
            $balanceBefore = (float) $row['balance_before'];
            $balanceAfter = (float) $row['balance_after'];
            $balanceChange = (float) $row['balance_change'];

            $transactionDetails = null;
            if (!empty($row['transaction_details'])) {
                $decoded = json_decode((string) $row['transaction_details'], true);
                $transactionDetails = is_array($decoded) ? $decoded : ['raw' => (string) $row['transaction_details']];
            }

            StatementRecord::updateOrCreate(
                ['uuid' => $uuid],
                [
                    'group_id' => $group->id,
                    'user_id' => $newUserId,
                    'expense_id' => $newExpenseId,
                    'settlement_id' => null,
                    'transaction_type' => (string) $row['transaction_type'],
                    'description' => (string) $row['description'],
                    'amount' => number_format($amount, 2, '.', ''),
                    'amount_cents' => $this->toCents($amount),
                    'reference_number' => (string) $row['reference_number'],
                    'balance_before' => number_format($balanceBefore, 2, '.', ''),
                    'balance_after' => number_format($balanceAfter, 2, '.', ''),
                    'balance_change' => number_format($balanceChange, 2, '.', ''),
                    'balance_before_cents' => $this->toCents($balanceBefore),
                    'balance_after_cents' => $this->toCents($balanceAfter),
                    'balance_change_cents' => $this->toCents($balanceChange),
                    'transaction_details' => $transactionDetails,
                    'transaction_date' => $row['transaction_date'],
                    'status' => (string) $row['status'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]
            );

            $count++;
        }

        return $count;
    }

    private function copyLegacyReceiptToPublicDisk(string $relativePath): bool
    {
        $filename = basename($relativePath);
        $source = public_path('uploads/receipts/' . $filename);
        $destination = storage_path('app/public/receipts/' . $filename);

        if (!File::exists($source)) {
            return false;
        }

        if (!File::exists(dirname($destination))) {
            File::ensureDirectoryExists(dirname($destination));
        }

        if (!File::exists($destination)) {
            File::copy($source, $destination);
        }

        return true;
    }

    /**
     * @return array<int>
     */
    private function decodeLegacyParticipantIds(mixed $value): array
    {
        if (!is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_map('intval', $decoded));
    }

    private function normalizeCategory(string $description): string
    {
        $text = strtolower(trim($description));

        if (Str::contains($text, ['grocery', 'freshco', 'fresco', 'foodbasic', 'coffe', 'coffee', 'walmart', 'wallmart'])) {
            return 'food';
        }
        if (Str::contains($text, ['utility', 'hydro', 'electricity', 'internet'])) {
            return 'utilities';
        }
        if (Str::contains($text, ['pressure cooker', 'dollerrama', 'parcel', 'household'])) {
            return 'shopping';
        }

        return 'other';
    }

    private function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function resolveLegacySqlPath(): string
    {
        $candidates = [
            base_path('old_splitmate.sql'),
            base_path('../old_splitmate.sql'),
        ];

        foreach ($candidates as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        throw new \RuntimeException('Could not locate old_splitmate.sql. Expected it at repo root or SplitMate-api root.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractInsertRows(string $sql, string $table): array
    {
        $pattern = sprintf('/INSERT INTO `%s`\\s*\\((.*?)\\)\\s*VALUES\\s*(.*?);/s', preg_quote($table, '/'));
        if (!preg_match($pattern, $sql, $matches)) {
            return [];
        }

        $columns = array_map(
            static fn (string $name): string => trim($name, " \t\n\r\0\x0B`"),
            explode(',', $matches[1])
        );

        $tuples = $this->splitSqlTuples($matches[2]);
        $rows = [];
        foreach ($tuples as $tuple) {
            $values = $this->splitTupleValues($tuple);
            if (count($values) !== count($columns)) {
                continue;
            }

            $row = [];
            foreach ($columns as $idx => $column) {
                $row[$column] = $this->parseSqlValue($values[$idx]);
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function splitSqlTuples(string $valuesPart): array
    {
        $tuples = [];
        $buffer = '';
        $depth = 0;
        $inQuote = false;
        $escape = false;

        $length = strlen($valuesPart);
        for ($i = 0; $i < $length; $i++) {
            $ch = $valuesPart[$i];

            if ($escape) {
                $buffer .= $ch;
                $escape = false;
                continue;
            }

            if ($inQuote && $ch === '\\') {
                $buffer .= $ch;
                $escape = true;
                continue;
            }

            if ($ch === "'") {
                $inQuote = !$inQuote;
                $buffer .= $ch;
                continue;
            }

            if (!$inQuote) {
                if ($ch === '(') {
                    if ($depth > 0) {
                        $buffer .= $ch;
                    }
                    $depth++;
                    continue;
                }
                if ($ch === ')') {
                    $depth--;
                    if ($depth === 0) {
                        $tuples[] = $buffer;
                        $buffer = '';
                        continue;
                    }
                }
            }

            if ($depth > 0) {
                $buffer .= $ch;
            }
        }

        return $tuples;
    }

    /**
     * @return array<int, string>
     */
    private function splitTupleValues(string $tuple): array
    {
        $fields = [];
        $buffer = '';
        $inQuote = false;
        $escape = false;

        $length = strlen($tuple);
        for ($i = 0; $i < $length; $i++) {
            $ch = $tuple[$i];

            if ($escape) {
                $buffer .= $ch;
                $escape = false;
                continue;
            }

            if ($inQuote && $ch === '\\') {
                $buffer .= $ch;
                $escape = true;
                continue;
            }

            if ($ch === "'") {
                $inQuote = !$inQuote;
                $buffer .= $ch;
                continue;
            }

            if (!$inQuote && $ch === ',') {
                $fields[] = trim($buffer);
                $buffer = '';
                continue;
            }

            $buffer .= $ch;
        }

        if ($buffer !== '') {
            $fields[] = trim($buffer);
        }

        return $fields;
    }

    private function parseSqlValue(string $raw): mixed
    {
        $raw = trim($raw);
        if (strtoupper($raw) === 'NULL') {
            return null;
        }

        if (strlen($raw) >= 2 && $raw[0] === "'" && $raw[strlen($raw) - 1] === "'") {
            $inner = substr($raw, 1, -1);
            return stripcslashes($inner);
        }

        if (is_numeric($raw)) {
            return str_contains($raw, '.') ? (float) $raw : (int) $raw;
        }

        return $raw;
    }

    private function legacyEmailFor(string $name, int $oldId): string
    {
        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = 'legacy-user-' . $oldId;
        }

        return "{$slug}.{$oldId}@legacy.splitmate.local";
    }

    private function deterministicUuid(string $type, string $legacyKey): string
    {
        $hash = md5("splitmate-legacy-{$type}-{$legacyKey}");
        $timeLow = substr($hash, 0, 8);
        $timeMid = substr($hash, 8, 4);
        $timeHiAndVersion = '4' . substr($hash, 13, 3);
        $clockSeqHiAndReserved = (hexdec(substr($hash, 16, 2)) & 0x3f) | 0x80;
        $clockSeqLow = substr($hash, 18, 2);
        $clockSeq = sprintf('%02x%s', $clockSeqHiAndReserved, $clockSeqLow);
        $node = substr($hash, 20, 12);

        return sprintf('%s-%s-%s-%s-%s', $timeLow, $timeMid, $timeHiAndVersion, $clockSeq, $node);
    }
}

