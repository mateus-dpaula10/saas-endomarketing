<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;

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
        return view ('tenant.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome'    => 'required|string|max:255',
            'dominio' => 'nullable|string|unique:tenants,dominio'
        ]);

        Tenant::create($request->only('nome', 'dominio'));

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
        $data = Tenant::findOrFail($id);
        
        return view ('tenant.edit', ['empresa' => $data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $empresa = Tenant::findOrFail($id);

        $request->validate([
            'nome'    => 'required|string|max:255',
            'dominio' => 'nullable|string|unique:tenants,dominio,' . $empresa->id
        ]);

        $empresa->update($request->only('nome', 'dominio'));

        return redirect()->route('empresa.index')->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $empresa = Tenant::findOrFail($id);

        $empresa->delete();

        return redirect()->route('empresa.index')->with('success', 'Empresa excluída com sucesso!');
    }
}
