<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAttachment extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'email_message_id',
        'gmail_attachment_id',
        'filename',
        'mime_type',
        'size',
    ];

    public function emailMessage(): BelongsTo
    {
        return $this->belongsTo(EmailMessage::class);
    }
}
