<?php

namespace App\Http\Controllers;

use App\Models\Diagnostic;
use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

class DiagnosticController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $diagnostics = Diagnostic::whereHas('tenants', function($query) use ($user) {
            $query->where('tenants.id', $user->tenant_id);
        })->get();

        return view ('diagnostic.index', compact('diagnostics'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenants = Tenant::all();

        return view ('diagnostic.create', compact('tenants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string',
            'description'   => 'nullable|string',
            'questions'     => 'required|array',
            'questions.*'   => 'required|string',
            'tenants'       => 'required|array',
            'tenants.*'     => 'exists:tenants,id',
        ]);

        $diagnostic = Diagnostic::create([
            'title'       => $request->title,
            'description' => $request->description
        ]);
        
        $diagnostic->tenants()->sync($request->tenants);

        foreach ($request->questions as $text) {
            $diagnostic->questions()->create(['text' => $text]);
        }

        return redirect()->route('diagnostico.index')->with('success', 'Diagnóstico criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Diagnostic $diagnostic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Diagnostic $diagnostic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Diagnostic $diagnostic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Diagnostic $diagnostic)
    {
        //
    }

    public function available() {
        $diagnostics = Diagnostic::whereNull('tenant_id')->with('questions')->get();

        return view ('diagnostic.availables', compact('diagnostics'));
    }

    public function answer(Request $request, $diagnosticId) {
        $diagnostic = Diagnostic::findOrFail($diagnosticId);
        $user = Auth::user();

        $alreadyAnswered = Answer::where('diagnostic_id', $diagnostic->id)
            ->where('tenant_id', $user->tenant_id)
            ->exists();

        if ($alreadyAnswered) {
            return redirect()->route('diagnostico.available')
                ->with('error', 'Este diagnóstico já foi respondido pela empresa.');
        }

        $request->validate([
            'answers'   => 'required|array',
            'answers.*' => 'required|integer|min:1|max:5'
        ]);

        foreach ($request->answers as $questionId => $note) {
            Answer::create([
                'user_id'       => $user->id,
                'diagnostic_id' => $diagnostic->id,
                'question_id'   => $questionId,
                'note'          => $note,
                'tenant_id'     => $user->tenant_id
            ]);
        }
        
        return redirect()->route('diagnostico.available')->with('success', 'Respostas enviadas com sucesso!');
    }
}
