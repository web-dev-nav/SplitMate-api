<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Expense extends Model
{
    protected $fillable = [
        'uuid',
        'group_id',
        'title',
        'description',
        'amount_cents',
        'amount',
        'paid_by_user_id',
        'receipt_photo',
        'expense_date',
        'category',
        'user_count_at_time',
        'participant_ids',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount_cents' => 'integer',
        'amount' => 'decimal:2',
        'participant_ids' => 'array',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function paidByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = Str::uuid();
            }
        });
    }
}
