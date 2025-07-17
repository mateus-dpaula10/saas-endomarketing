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
        $request->validate([
            'nome'       => 'required|string|max:255|unique:tenants,nome',
            'plain_id'   => 'required|exists:plains,id'
        ]);

        Tenant::create($request->only('nome', 'plain_id'));

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

        $request->validate([
            'nome'       => 'required|string|max:255|unique:tenants,nome,' . $empresa->id,
            'plain_id'   => 'required|exists:plains,id'
        ]);
        
        $oldPlainId = $empresa->plain_id;
        $newPlainId = $request->plain_id;        

        if ($oldPlainId != $newPlainId) {            
            $oldDiagnostics = Diagnostic::where('plain_id', $oldPlainId)->get();
            foreach ($oldDiagnostics as $diagnostic) {
                $diagnostic->tenants()->detach($empresa->id);
                $diagnostic->periods()->where('tenant_id', $empresa->id)->delete();
            }
            
            $newDiagnostics = Diagnostic::where('plain_id', $newPlainId)->get();
            foreach ($newDiagnostics as $diagnostic) {                
                $diagnostic->tenants()->syncWithoutDetaching($empresa->id);

                $originalPeriod = $diagnostic->periods->first();

                if (!$originalPeriod) {
                    $diagnostic->periods()->updateOrCreate(
                        ['tenant_id' => $empresa->id],
                        [
                            'start' => now(),
                            'end'   => now()->addDays(2)
                        ]
                    );
                } else {
                    $diagnostic->periods()->updateOrCreate(
                        ['tenant_id' => $empresa->id],
                        [
                            'start' => $originalPeriod->start,
                            'end'   => $originalPeriod->end,
                        ]
                    );
                }                
            }
        }

        $empresa->update($request->only('nome', 'plain_id'));

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
