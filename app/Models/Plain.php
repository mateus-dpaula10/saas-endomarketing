<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plain extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'price', 'description'];

    public function tenants() {
        return $this->hasMany(Tenant::class);
    }

    public function diagnostics() {
        return $this->hasMany(Diagnostic::class);
    }
}
