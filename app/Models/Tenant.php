<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;
    protected $fillable = ['nome', 'plain_id'];

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
