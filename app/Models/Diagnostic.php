<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Diagnostic extends Model
{
    use HasFactory;
    protected $fillable = ['tenant_id', 'title', 'description'];

    public function questions() {
        return $this->belongsToMany(Question::class)->withPivot('target')->withTimestamps();
    }

    public function answers() {
        return $this->hasMany(Answer::class);
    }

    public function tenants() {
        return $this->belongsToMany(Tenant::class);
    }

    public function periods() {
        return $this->hasMany(DiagnosticPeriod::class);
    }
}
