<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Diagnostic extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'plain_id'];

    protected $casts = ['pivot.target' => 'array'];

    public function questions() {
        return $this->belongsToMany(Question::class, 'diagnostic_question')
            ->withPivot('target')
            ->withTimestamps();
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

    public function campaigns() {
        return $this->hasMany(Campaign::class);
    }

    public function plain() {
        return $this->belongsTo(Plain::class);
    }
}