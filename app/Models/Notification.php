<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{    
    protected $table = 'notifications';
        
    public $timestamps = false;
    
    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'created_at',
    ];
    
    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
        'read_at' => 'datetime',
    ];
}
