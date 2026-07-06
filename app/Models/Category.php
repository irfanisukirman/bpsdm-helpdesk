<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'routing_role',
        'routing_bidang',
        'notify_email',
        'is_active',
        'lms_category_ref',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }

    public function activeSubcategories(): HasMany
    {
        return $this->subcategories()->where('is_active', true);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
