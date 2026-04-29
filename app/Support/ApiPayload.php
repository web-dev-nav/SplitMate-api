<?php

namespace App\Support;

use App\Models\Expense;
use App\Models\Group;
use App\Models\Settlement;
use App\Models\StatementRecord;
use App\Models\User;
class ApiPayload
{
    private static function publicMediaUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return route('media.public', ['path' => ltrim((string) $path, '/')]);
    }

    public static function user(User $user): array
    {
        return [
            'id' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'is_google_account' => !empty($user->google_id),
            'is_active' => (bool) $user->is_active,
            'email_verified_at' => optional($user->email_verified_at)?->toIso8601String(),
            'created_at' => optional($user->created_at)?->toIso8601String(),
            'updated_at' => optional($user->updated_at)?->toIso8601String(),
        ];
    }

    public static function group(Group $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'invite_code' => $group->invite_code,
            'currency_code' => $group->currency_code,
            'expense_categories' => $group->expense_categories ?? \App\Models\Group::defaultExpenseCategories(),
            'created_by_user_id' => optional($group->creator)?->uuid ?? (string) $group->created_by_user_id,
            'email_notifications' => (bool) ($group->email_notifications ?? false),
            'created_at' => optional($group->created_at)?->toIso8601String(),
            'updated_at' => optional($group->updated_at)?->toIso8601String(),
        ];
    }

    public static function groupMember(User $user): array
    {
        return [
            'id' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => (bool) ($user->pivot->is_active ?? $user->is_active),
            'role' => $user->pivot->role ?? null,
            'joined_at' => optional($user->pivot->joined_at ?? null)?->toIso8601String(),
        ];
    }

    public static function expense(Expense $expense): array
    {
        return [
            'id' => $expense->uuid,
            'group_id' => $expense->group_id,
            'title' => $expense->title,
            'amount_cents' => (int) $expense->amount_cents,
            'paid_by_user_id' => optional($expense->paidByUser)?->uuid,
            'paid_by_user_name' => optional($expense->paidByUser)?->name,
            'expense_date' => optional($expense->expense_date)?->toDateString(),
            'category' => $expense->category,
            'participant_ids' => $expense->participant_ids ?? [],
            'receipt_photo_url' => self::publicMediaUrl($expense->receipt_photo),
            'created_at' => optional($expense->created_at)?->toIso8601String(),
            'updated_at' => optional($expense->updated_at)?->toIso8601String(),
        ];
    }

    public static function settlement(Settlement $settlement): array
    {
        return [
            'id' => $settlement->uuid,
            'group_id' => $settlement->group_id,
            'from_user_id' => optional($settlement->fromUser)?->uuid,
            'from_user_name' => optional($settlement->fromUser)?->name,
            'to_user_id' => optional($settlement->toUser)?->uuid,
            'to_user_name' => optional($settlement->toUser)?->name,
            'amount_cents' => (int) $settlement->amount_cents,
            'proof_photo_url' => self::publicMediaUrl($settlement->proof_photo),
            'settlement_date' => optional($settlement->settlement_date)?->toDateString(),
            'created_at' => optional($settlement->created_at)?->toIso8601String(),
            'updated_at' => optional($settlement->updated_at)?->toIso8601String(),
        ];
    }

    public static function statement(StatementRecord $record): array
    {
        $expensePayer = optional(optional($record->expense)->paidByUser)->name;
        $settlementFrom = optional(optional($record->settlement)->fromUser)->name;
        $settlementTo = optional(optional($record->settlement)->toUser)->name;

        return [
            'id' => $record->uuid ?? (string) $record->id,
            'user_id' => optional($record->user)?->uuid ?? (string) $record->user_id,
            'user_name' => optional($record->user)?->name,
            'type' => $record->transaction_type,
            'description' => $record->description,
            'amount_cents' => (int) ($record->amount_cents ?? 0),
            'balance_before_cents' => (int) ($record->balance_before_cents ?? 0),
            'balance_after_cents' => (int) ($record->balance_after_cents ?? 0),
            'balance_change_cents' => (int) ($record->balance_change_cents ?? 0),
            'paid_by_user_name' => $expensePayer,
            'from_user_name' => $settlementFrom,
            'to_user_name' => $settlementTo,
            'transaction_date' => optional($record->transaction_date)?->toIso8601String(),
            'created_at' => optional($record->created_at)?->toIso8601String(),
        ];
    }
}
