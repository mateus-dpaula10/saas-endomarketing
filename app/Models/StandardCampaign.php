<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StandardCampaign extends Model
{
    use HasFactory;

    protected $table = 'standard_campaigns';

    protected $fillable = [
        'category_code',
        'trigger_max_score',
        'text',
        'description',
        'is_active',
    ];

    protected $casts = [
        'trigger_max_score' => 'float',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function content()
    {
        return $this->hasOne(StandardCampaignContent::class);
    }
}
