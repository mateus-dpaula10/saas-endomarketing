<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Plain;
use App\Models\Diagnostic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $authUser = auth()->user();

        if ($authUser->role === 'superadmin') {
            $empresas = Tenant::all();
        } elseif ($authUser->role === 'admin') {
            $empresas = Tenant::where('id', $authUser->tenant_id)->get();
        }

        return view ('tenant.index', compact('authUser', 'empresas'));
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
        $validator = Validator::make($request->all(), [
            'nome'                    => 'required|string|max:255|unique:tenants,nome',
            'plain_id'                => 'required|exists:plains,id',
            'cnpj'                    => 'required|string|size:14|unique:tenants,cnpj',
            'social_reason'           => 'nullable|string|max:255',
            'fantasy_name'            => 'nullable|string|max:255',
            'address'                 => 'nullable|string|max:255',
            'bairro'                  => 'nullable|string|max:255',
            'cep'                     => 'nullable|string|max:10',
            'telephone'               => 'nullable|string|max:20',
            'contract_start'          => 'required|date',
            'active_tenant'           => 'required|boolean'
        ], [
            'nome.required'           => 'O campo nome é obrigatório.',
            'nome.unique'             => 'Já existe uma empresa cadastrada com esse nome.',            
            'plain_id.required'       => 'É obrigatório selecionar um plano.',
            'plain_id.exists'         => 'O plano selecionado não é válido.',
            'cnpj.required'           => 'O campo CNPJ é obrigatório.',
            'cnpj.size'               => 'O CNPJ deve ter exatamente 14 caracteres numéricos.',
            'cnpj.unique'             => 'Já existe uma empresa cadastrada com esse CNPJ.',
            'social_reason.max'       => 'A razão social deve ter no máximo 255 caracteres.',
            'fantasy_name.max'        => 'O nome fantasia deve ter no máximo 255 caracteres.',
            'address.max'             => 'O endereço deve ter no máximo 255 caracteres.',
            'bairro.max'              => 'O bairro deve ter no máximo 255 caracteres.',
            'cep.max'                 => 'O cEP deve ter no máximo 10 caracteres.',
            'telephone.max'           => 'O telefone deve ter no máximo 20 caracteres.',
            'contract_start.required' => 'A data de início do contrato é obrigatória.',
            'contract_start.date'     => 'A data de início do contrato deve ser válida.',
            'active_tenant.required'  => 'É obrigatório informar se a empresa está ativa.',
            'active_tenant.boolean'   => 'O campo ativo deve ser verdadeiro ou falso.'
        ]); 

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $validated['cnpj'] = preg_replace('/\D/', '', $validated['cnpj']);

        $tenant = Tenant::create($validated);

        $plan = Plain::with('diagnostics')->find($validated['plain_id']);
        if ($plan && $plan->diagnostics->isNotEmpty()) {
            $tenant->diagnostics()->syncWithoutDetaching($plan->diagnostics->pluck('id'));
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

    private function criarPeriodosParaEmpresa(Tenant $empresa, int $plainId) {
        $empresa->diagnostics->each(function ($diagnostic) use ($empresa) {
            $diagnostic->periods()->where('tenant_id', $empresa->id)->delete();
        });

        $plan = Plain::with('diagnostics')->find($plainId);
        if (!$plan || $plan->diagnostics->isEmpty()) {
            return;
        }

        $start = now()->startOfDay();

        foreach ($plan->diagnostics as $diagnostic) {
            if ($plan->isAvulso()) {
                $diagnostic->periods()->create([
                    'tenant_id' => $empresa->id,
                    'start'     => $start,
                    'end'       => $start->copy()->addDays(29)->endOfDay()
                ]);
            }
            
            if ($plan->isMensal()) {
                $characteristics = $plan->characteristics ?? [];
                $diagnosticsPerMonth = $characteristics['diagnostics_per_month'] ?? 1;

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

                for ($i = 0; $i < $periodCount; $i++) {
                    $periodStart = (clone $start)->addDays($duration * $i);
                    $periodEnd   = (clone $periodStart)->addDays($duration - 1);

                    $diagnostic->periods()->create([
                        'tenant_id' => $empresa->id,
                        'start'     => $periodStart,
                        'end'       => $periodEnd,
                    ]);
                }
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $empresa = Tenant::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nome'                    => 'required|string|max:255|unique:tenants,nome,' . $empresa->id,
            'plain_id'                => 'required|exists:plains,id',
            'cnpj'                    => 'required|string|size:14|unique:tenants,cnpj,' . $empresa->id,
            'social_reason'           => 'nullable|string|max:255',
            'fantasy_name'            => 'nullable|string|max:255',
            'address'                 => 'nullable|string|max:255',
            'bairro'                  => 'nullable|string|max:255',
            'cep'                     => 'nullable|string|max:10',
            'telephone'               => 'nullable|string|max:20',
            'contract_start'          => 'required|date',
            'active_tenant'           => 'required|boolean'
        ], [
            'nome.required'           => 'O campo nome é obrigatório.',
            'nome.unique'             => 'Já existe uma empresa cadastrada com esse nome.',            
            'plain_id.required'       => 'É obrigatório selecionar um plano.',
            'plain_id.exists'         => 'O plano selecionado não é válido.',
            'cnpj.required'           => 'O campo CNPJ é obrigatório.',
            'cnpj.size'               => 'O CNPJ deve ter exatamente 14 caracteres numéricos.',
            'cnpj.unique'             => 'Já existe uma empresa cadastrada com esse CNPJ.',            
            'social_reason.max'       => 'A razão social deve ter no máximo 255 caracteres.',
            'fantasy_name.max'        => 'O nome fantasia deve ter no máximo 255 caracteres.',
            'address.max'             => 'O endereço deve ter no máximo 255 caracteres.',
            'bairro.max'              => 'O bairro deve ter no máximo 255 caracteres.',
            'cep.max'                 => 'O cEP deve ter no máximo 10 caracteres.',
            'telephone.max'           => 'O telefone deve ter no máximo 20 caracteres.',
            'contract_start.required' => 'A data de início do contrato é obrigatória.',
            'contract_start.date'     => 'A data de início do contrato deve ser válida.',
            'active_tenant.required'  => 'É obrigatório informar se a empresa está ativa.',
            'active_tenant.boolean'   => 'O campo ativo deve ser verdadeiro ou falso.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $validated['cnpj'] = preg_replace('/\D/', '', $validated['cnpj']);
        $validated['cep'] = isset($validated['cep']) ? preg_replace('/\D/', '', $validated['cep']) : null;
        $validated['telephone'] = isset($validated['telephone']) ? preg_replace('/\D/', '', $validated['telephone']) : null;
        
        $oldPlainId = $empresa->plain_id;
        $newPlainId = $validated['plain_id'];        

        $empresa->update($validated);

        if ($oldPlainId != $newPlainId) {       
            $this->criarPeriodosParaEmpresa($empresa, $newPlainId);

            $novoDiagnostico = Diagnostic::where('plain_id', $newPlainId)->first();
            if ($novoDiagnostico) {
                $empresa->diagnostics()->sync([$novoDiagnostico->id]);

                DB::table('diagnostic_periods')
                    ->where('tenant_id', $empresa->id)
                    ->update(['diagnostic_id' => $novoDiagnostico->id]);
            }
        } else {
            if ($validated['active_tenant']) {
                $this->criarPeriodosParaEmpresa($empresa, $newPlainId);
            }
        }

        if (!$validated['active_tenant']) {
            $empresa->diagnostics->each(function ($diagnostic) use ($empresa) {
                $diagnostic->periods()->where('tenant_id', $empresa->id)->delete();
            });
        }

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
