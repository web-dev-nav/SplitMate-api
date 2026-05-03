<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StatementRecord extends Model
{
    protected $fillable = [
        'uuid',
        'group_id',
        'user_id',
        'expense_id',
        'settlement_id',
        'transaction_type',
        'description',
        'amount',
        'amount_cents',
        'reference_number',
        'balance_before',
        'balance_after',
        'balance_change',
        'balance_before_cents',
        'balance_after_cents',
        'balance_change_cents',
        'transaction_details',
        'transaction_date',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_cents' => 'integer',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'balance_change' => 'decimal:2',
        'balance_before_cents' => 'integer',
        'balance_after_cents' => 'integer',
        'balance_change_cents' => 'integer',
        'transaction_details' => 'array',
        'transaction_date' => 'datetime'
    ];

    /**
     * Get the group that this record belongs to
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the user that owns this statement record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the expense that created this record (if applicable)
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Get the settlement that created this record (if applicable)
     */
    public function settlement(): BelongsTo
    {
        return $this->belongsTo(Settlement::class);
    }

    /**
     * Generate a unique reference number for this transaction
     */
    public static function generateReferenceNumber($type = 'TXN'): string
    {
        $prefix = strtoupper((string) $type) . date('Ymd');
        $latestReference = static::query()
            ->where('reference_number', 'like', $prefix . '%')
            ->orderByDesc('reference_number')
            ->value('reference_number');

        $nextSequence = 1;
        if (is_string($latestReference) && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $latestReference, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        do {
            $candidate = $prefix . str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);
            $nextSequence++;
        } while (static::query()->where('reference_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * Scope to get records for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get records by transaction type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope to get records within date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Get formatted balance change with + or - sign
     */
    public function getFormattedBalanceChangeAttribute(): string
    {
        return ($this->balance_change >= 0 ? '+' : '') . '$' . number_format($this->balance_change, 2);
    }

    /**
     * Get formatted running balance with + or - sign
     */
    public function getFormattedBalanceAfterAttribute(): string
    {
        return ($this->balance_after >= 0 ? '+' : '') . '$' . number_format($this->balance_after, 2);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = Str::uuid();
            }

            if (($model->amount === null || $model->amount === '') && $model->amount_cents !== null) {
                $model->amount = round(((int) $model->amount_cents) / 100, 2);
            }

            if (($model->balance_before === null || $model->balance_before === '') && $model->balance_before_cents !== null) {
                $model->balance_before = round(((int) $model->balance_before_cents) / 100, 2);
            }

            if (($model->balance_after === null || $model->balance_after === '') && $model->balance_after_cents !== null) {
                $model->balance_after = round(((int) $model->balance_after_cents) / 100, 2);
            }

            if (($model->balance_change === null || $model->balance_change === '') && $model->balance_change_cents !== null) {
                $model->balance_change = round(((int) $model->balance_change_cents) / 100, 2);
            }
        });
    }
}
