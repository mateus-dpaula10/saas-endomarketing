<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosticTenant extends Model
{
    use HasFactory;

    protected $table = 'diagnostic_tenant';

    protected $fillable = [
        'tenant_id',
        'diagnostic_id',
        'start_date',
        'end_date',
        'status',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function diagnostic()
    {
        return $this->belongsTo(Diagnostic::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
