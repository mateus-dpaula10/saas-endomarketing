<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComparativeRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'diagnostic_quadrant_analysis_id',
        'elemento',
        'colaboradores',
        'gestao'
    ];

    public function analysis()
    {
        return $this->belongsTo(DiagnosticQuadrantAnalysis::class, 'diagnostic_quadrant_analysis_id');
    }
}
