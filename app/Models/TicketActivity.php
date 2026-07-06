<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketActivity extends Model
{
    protected $fillable = [
        'ticket_id',
        'actor_type',
        'user_id',
        'action',
        'from_status',
        'to_status',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return match ($this->action) {
            'dibuat' => 'Tiket dibuat',
            'didistribusikan' => 'Didistribusikan ke bidang',
            'mulai_diproses' => 'Mulai diproses',
            'selesai' => 'Tiket diselesaikan',
            'dibuka_kembali' => 'Tiket dibuka kembali',
            'redistribusi' => 'Tiket diredistribusi',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
