<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ['nome', 'dominio'];

    public function users() {
        return $this->hasMany(User::class);
    }

    public function diagnostics() {
        return $this->belongsToMany(Diagnostic::class, 'diagnostic_tenant');
    }
}
