<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Answer extends Model
{
    use HasFactory;
    protected $fillable = ['diagnostic_id', 'question_id', 'user_id', 'note', 'tenant_id', 'diagnostic_period_id'];

    public function question() {
        return $this->belongsTo(Question::class);
    }

    public function diagnosticPeriod() {
        return $this->belongsTo(DiagnosticPeriod::class);
    }

    public function diagnostic() {
        return $this->belongsTo(Diagnostic::class);
    }

    public function period() {
        return $this->belongsTo(DiagnosticPeriod::class, 'diagnostic_period_id');
    }
}
