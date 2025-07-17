<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StandardCampaignContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'standard_campaign_id',
        'goal',
        'video_url',
        'image_url',
        'actions',
        'resources',
        'quiz',
    ];

    protected $casts = [
        'actions'   => 'array',
        'resources' => 'array',
        'quiz'      => 'array',
    ];

    public function standardCampaign()
    {
        return $this->belongsTo(StandardCampaign::class);
    }

    public function plain() {
        return $this->belongsTo(Plain::class);
    }
}
