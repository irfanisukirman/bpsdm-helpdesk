<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketReminder extends Model
{
    protected $fillable = [
        'ticket_id',
        'stage',
        'sent_at',
    ];

    protected $casts = [
        'stage' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
