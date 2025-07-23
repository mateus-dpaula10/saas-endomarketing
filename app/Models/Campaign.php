<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'standard_campaign_id',
        'diagnostic_id',
        'text',
        'description',
        'start_date',
        'end_date',
        'is_auto',
        'is_manual',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_auto' => 'boolean',
        'is_manual' => 'boolean',
        'actions' => 'array',
        'resources' => 'array',
        'quiz' => 'array',
    ];

    public function standardCampaign()
    {
        return $this->belongsTo(StandardCampaign::class, 'standard_campaign_id');
    }

    public function diagnostic() {
        return $this->belongsTo(Diagnostic::class);
    }

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }
}
