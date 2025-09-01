<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plain extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'type',
        'price',
        'includes_campaigns',
        'characteristics'
    ];

    protected $casts = [
        'characteristics'    => 'array',
        'includes_campaigns' => 'boolean'
    ];

    public function tenants() {
        return $this->hasMany(Tenant::class);
    }

    public function diagnostics() {
        return $this->hasMany(Diagnostic::class);
    }

    public function isAvulso(): bool {
        return $this->type === 'avulso';
    }

    public function isMensal(): bool {
        return $this->type === 'mensal';
    }
}
