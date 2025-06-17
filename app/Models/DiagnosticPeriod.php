<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiagnosticPeriod extends Model
{
    use HasFactory;
    protected $fillable = ['diagnostic_id', 'tenant_id', 'start', 'end'];

    public function diagnostic() {
        return $this->belongsTo(Diagnostic::class);
    }

    public function answers() {
        return $this->hasMany(Answer::class, 'diagnostic_period_id');
    }
}
