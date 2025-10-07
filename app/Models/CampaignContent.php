<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignContent extends Model
{
    protected $fillable = ['campaign_id', 'type', 'content', 'file_path'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
