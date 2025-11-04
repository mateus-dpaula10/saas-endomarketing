<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosticQuadrantAnalysis extends Model
{
    use HasFactory;

    protected $table = 'diagnostic_quadrant_analyses';

    protected $fillable = [
        'tenant_id',
        'diagnostic_id',
        'role',
        'medias',
        'classificacao',
        'sinais',
        'resumo',
        'resumo_geral'
    ];

    protected $casts = [
        'medias' => 'array',
        'classificacao' => 'array',
        'sinais' => 'array',
    ];

    public function diagnostic()
    {
        return $this->belongsTo(Diagnostic::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
