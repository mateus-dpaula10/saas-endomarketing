<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{    
    protected $table = 'notifications';
        
    public $timestamps = true;
    
    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'tenant_id'
    ];
    
    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
