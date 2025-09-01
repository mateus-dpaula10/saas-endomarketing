<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Answer extends Model
{
    use HasFactory;
    
    protected $fillable = ['diagnostic_id', 'question_id', 'user_id', 'note', 'tenant_id'];

    public function question() {
        return $this->belongsTo(Question::class);
    }

    public function diagnostic() {
        return $this->belongsTo(Diagnostic::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }
}
