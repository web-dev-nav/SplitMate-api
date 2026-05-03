<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Group extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'invite_code',
        'qr_join_token',
        'created_by_user_id',
        'currency_code',
        'expense_categories',
        'email_notifications',
    ];

    protected $hidden = [];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'expense_categories' => 'array',
            'email_notifications' => 'boolean',
        ];
    }

    /**
     * Get the route key name.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Get the creator of the group.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the members of the group.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withPivot('role', 'is_active', 'expense_email_notifications', 'settlement_email_notifications', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get the expenses in this group.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the settlements in this group.
     */
    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    /**
     * Get the statement records in this group.
     */
    public function statementRecords(): HasMany
    {
        return $this->hasMany(StatementRecord::class);
    }

    /**
     * Generate a unique invite code.
     */
    public static function generateInviteCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('invite_code', $code)->exists());

        return $code;
    }

    /**
     * Generate a unique token used for QR-based joins.
     */
    public static function generateQrJoinToken(): string
    {
        do {
            $token = Str::random(48);
        } while (self::where('qr_join_token', $token)->exists());

        return $token;
    }

    public static function defaultExpenseCategories(): array
    {
        return [
            'food',
            'transport',
            'entertainment',
            'utilities',
            'accommodation',
            'shopping',
            'healthcare',
            'other',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = Str::uuid();
            }
        });
    }
}
