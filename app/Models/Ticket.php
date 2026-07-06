<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'reporter_name',
        'reporter_nip',
        'reporter_email',
        'reporter_whatsapp',
        'category_id',
        'subcategory_id',
        'title',
        'description',
        'priority',
        'status',
        'assigned_bidang',
        'handled_by',
        'analysis',
        'follow_up',
        'resolution',
        'reopened_count',
        'first_processed_at',
        'resolved_at',
        'lms_user_id',
    ];

    protected $casts = [
        'priority' => Priority::class,
        'status' => TicketStatus::class,
        'reopened_count' => 'integer',
        'first_processed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // --- Relasi ---

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(TicketNote::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class)->oldest();
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(TicketReminder::class);
    }

    // --- Scope ---

    public function scopeForBidang(Builder $query, string $bidang): Builder
    {
        return $query->where('assigned_bidang', $bidang);
    }

    public function scopeStatus(Builder $query, TicketStatus|string $status): Builder
    {
        return $query->where('status', $status instanceof TicketStatus ? $status->value : $status);
    }

    // --- Pembantu ---

    public function isResolved(): bool
    {
        return $this->status === TicketStatus::Selesai;
    }

    public function getRouteKeyName(): string
    {
        return 'ticket_number';
    }
}
