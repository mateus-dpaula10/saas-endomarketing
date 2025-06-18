<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;
    protected $fillable = ['diagnostic_id', 'text', 'category', 'target'];

    public function diagnostic() {
        return $this->belongsTo(Diagnostic::class);
    }
}
