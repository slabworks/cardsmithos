<?php

namespace App\Models;

use App\Enums\ConversationStatus;
use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'customer_id',
        'access_token',
        'guest_name',
        'guest_email',
        'subject',
        'status',
        'last_message_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ConversationStatus::class,
            'last_message_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<Message>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /**
     * @return HasOne<Message>
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function participantName(): string
    {
        return $this->customer?->name ?? $this->guest_name ?? 'Unknown';
    }

    public function participantEmail(): string
    {
        return $this->customer?->email ?? $this->guest_email ?? '';
    }

    public function ensureAccessToken(): string
    {
        if ($this->access_token === null || $this->access_token === '') {
            $this->access_token = Str::random(64);
            $this->save();
        }

        return $this->access_token;
    }

    public function rotateAccessToken(): string
    {
        $this->access_token = Str::random(64);
        $this->save();

        return $this->access_token;
    }

    public function isOpen(): bool
    {
        return $this->status === ConversationStatus::Open;
    }
}
