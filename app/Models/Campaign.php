<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = ['category_id', 'title', 'description', 'active'];

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'campaign_tenant', 'campaign_id', 'tenant_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function contents()
    {
        return $this->hasMany(CampaignContent::class);
    }
}
