<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Plain;
use App\Models\Diagnostic;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Tenant::all();
        return view ('tenant.index', ['empresas' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plains = Plain::get();        

        return view ('tenant.create', compact('plains'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome'                 => 'required|string|max:255|unique:tenants,nome',
            'plain_id'             => 'required|exists:plains,id',
            'cnpj'                 => 'required|string|size:14',
            'social_reason'        => 'nullable|string|max:255',
            'fantasy_name'         => 'nullable|string|max:255',
            'address'              => 'nullable|string|max:255',
            'bairro'               => 'nullable|string|max:255',
            'cep'                  => 'nullable|string|max:10',
            'telephone'            => 'nullable|string|max:20',
            'contract_start'       => 'required|date',
            'active_tenant'        => 'required|boolean'
        ]); 

        $validated['cnpj'] = preg_replace('/\D/', '', $validated['cnpj']);

        $tenant = Tenant::create($validated);

        $diagnostics = Plain::find($validated['plain_id'])?->diagnostics;

        if ($diagnostics && $diagnostics->isNotEmpty()) {
            $tenant->diagnostics()->syncWithoutDetaching($diagnostics->pluck('id'));

            $characteristics = $tenant->plain->characteristics;

            $diagnosticsPerMonth = $characteristics['diagnostics_per_month'] ?? 0;

            $periodCount = match ($diagnosticsPerMonth) {
                1       => 1,
                2       => 2,
                3       => 3,
                default => 1
            };

            $duration = match ($diagnosticsPerMonth) {
                1       => 30,
                2       => 15,
                3       => 10,
                default => 30
            };

            $start = now()->startOfDay();

            foreach ($diagnostics as $diagnostic) {
                for ($i = 0; $i < $periodCount; $i++) {
                    $periodStart = (clone $start)->addDays($duration * $i);
                    $periodEnd   = (clone $periodStart)->addDays($duration - 1);

                    $diagnostic->periods()->create([
                        'tenant_id' => $tenant->id,
                        'start'     => $periodStart,
                        'end'       => $periodEnd
                    ]);
                }
            }
        }

        return redirect()->route('empresa.index')->with('success', 'Empresa criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Tenant::with('plain')->findOrFail($id);
        $plains = Plain::all();
        
        return view ('tenant.edit', [
            'empresa' => $data,
            'plains'  => $plains
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $empresa = Tenant::findOrFail($id);

        $validated = $request->validate([
            'nome'                 => 'required|string|max:255|unique:tenants,nome,' . $empresa->id,
            'plain_id'             => 'required|exists:plains,id',
            'cnpj'                 => 'required|string|size:14',
            'social_reason'        => 'nullable|string|max:255',
            'fantasy_name'         => 'nullable|string|max:255',
            'address'              => 'nullable|string|max:255',
            'bairro'               => 'nullable|string|max:255',
            'cep'                  => 'nullable|string|max:10',
            'telephone'            => 'nullable|string|max:20',
            'contract_start'       => 'required|date',
            'active_tenant'        => 'required|boolean'
        ]);

        $validated['cnpj'] = preg_replace('/\D/', '', $validated['cnpj']);
        $validated['cep'] = isset($validated['cep']) ? preg_replace('/\D/', '', $validated['cep']) : null;
        $validated['telephone'] = isset($validated['telephone']) ? preg_replace('/\D/', '', $validated['telephone']) : null;
        
        $oldPlainId = $empresa->plain_id;
        $newPlainId = $validated['plain_id'];        

        if ($oldPlainId != $newPlainId) {            
            $oldDiagnostics = Diagnostic::where('plain_id', $oldPlainId)->get();

            foreach ($oldDiagnostics as $diagnostic) {
                $diagnostic->tenants()->detach($empresa->id);
                $diagnostic->periods()->where('tenant_id', $empresa->id)->delete();
            }
            
            $newDiagnostics = Diagnostic::where('plain_id', $newPlainId)->get();

            foreach ($newDiagnostics as $diagnostic) {                
                $diagnostic->tenants()->syncWithoutDetaching([$empresa->id]);

                $period = $diagnostic->periods()->where('tenant_id', $empresa->id)->first();
                    
                $diagnostic->periods()->updateOrCreate(
                    ['tenant_id' => $empresa->id],
                    [
                        'start' => $period?->start ?? now(),
                        'end'   => $period?->end ?? now()->addDays(2)
                    ]
                );              
            }
        }

        $empresa->update($validated);

        return redirect()->route('empresa.index')->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $empresa = Tenant::with('users', 'diagnostics')->findOrFail($id);

        $empresa->delete();

        return redirect()->route('empresa.index')->with('success', 'Empresa excluída com sucesso!');
    }
}
