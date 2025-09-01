<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'text', 
        'category', 
        'type',     
        'diagnostic_type'
    ];

    public function diagnostics()
    {
        return $this->belongsToMany(Diagnostic::class, 'diagnostic_question');
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
