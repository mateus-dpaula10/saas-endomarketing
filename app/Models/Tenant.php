<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nome', 
        'plain_id',
        'cnpj',
        'telephone',
        'cep',
        'bairro',
        'address',
        'social_reason',
        'fantasy_name',
        'active_tenant',
        'contract_start'
    ];

    protected $casts = [
        'contract_start' => 'date',
        'active_tenant'  => 'boolean'
    ];

    public function users() {
        return $this->hasMany(User::class);
    }

    public function diagnostics() {
        return $this->belongsToMany(Diagnostic::class, 'diagnostic_tenant');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function plain() {
        return $this->belongsTo(Plain::class);
    }
}