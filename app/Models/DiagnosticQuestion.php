<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DiagnosticQuestion extends Pivot
{
    protected $table = 'diagnostic_questions';

    protected $casts = [
        'target' => 'array',
    ];
}
