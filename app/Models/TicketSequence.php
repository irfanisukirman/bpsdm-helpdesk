<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketSequence extends Model
{
    protected $table = 'ticket_sequences';

    protected $primaryKey = 'seq_date';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'seq_date',
        'last_number',
    ];

    protected $casts = [
        'last_number' => 'integer',
    ];
}
