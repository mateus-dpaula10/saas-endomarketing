<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;
    protected $fillable = ['text', 'category'];

    public function diagnostics() {
        return $this->belongsToMany(Diagnostic::class, 'diagnostic_question')
            ->withPivot('target')
            ->withTimestamps();
    }
}
