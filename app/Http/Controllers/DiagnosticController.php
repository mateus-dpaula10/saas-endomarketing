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

        if ($user->role === 'superadmin') {
            $diagnostics = Diagnostic::all();
        } else {
            $diagnostics = Diagnostic::whereHas('tenants', function($query) use ($user) {
                $query->where('tenants.id', $user->tenant_id);
            })->get();  
        }        

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
            'tenants.*'     => 'exists:tenants,id'
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
    public function edit(string $id)
    {
        $diagnostic = Diagnostic::with('questions', 'tenants')->findOrFail($id);
        $tenants = Tenant::all();

        return view ('diagnostic.edit', compact(['diagnostic', 'tenants']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $diagnostic = Diagnostic::findOrFail($id);

        $request->validate([
            'title'          => 'required|string',
            'description'    => 'nullable|string',
            'questions'      => 'required|array',
            'questions.*'    => 'required|string',
            'tenants'        => 'required|array',
            'tenants.*'      => 'exists:tenants,id',
            'question_ids'   => 'array',
            'question_ids.*' => 'nullable|integer|exists:questions,id'
        ]);

        $diagnostic->update([
            'title'       => $request->title,
            'description' => $request->description
        ]);

        $diagnostic->tenants()->sync($request->tenants);

        $questionIds = $request->input('question_ids', []);
        $questions = $request->input('questions');

        $existingQuestionIds = $diagnostic->questions()->pluck('id')->toArray();

        $submittedIds = [];

        foreach ($questions as $index => $text) {
            if (!empty($questionIds[$index])) {
                $question = $diagnostic->questions()->find($questionIds[$index]);
                if ($question) {
                    $question->update(['text' => $text]);
                    $submittedIds[] = $question->id;
                }
            } else {
                $newQuestion = $diagnostic->questions()->create(['text' => $text]);
                $submittedIds[] = $newQuestion->id;
            }
        }

        $toDelete = array_diff($existingQuestionIds, $submittedIds);
        if (!empty($toDelete)) {
            $diagnostic->questions()->whereIn('id', $toDelete)->delete();
        }

        return redirect()->route('diagnostico.index')->with('success', 'Diagnóstico atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $diagnostic = Diagnostic::with('questions', 'answers', 'tenants')->findOrFail($id);

        $diagnostic->delete();

        return redirect()->route('diagnostico.index')->with('success', 'Diagnóstico excluído com sucesso!');
    }

    // public function available() {
    //     $diagnostics = Diagnostic::whereNull('tenant_id')->with('questions')->get();

    //     return view ('diagnostic.availables', compact('diagnostics'));
    // }

    public function showAnswerForm(string $id) {
        $user = Auth::user();
        $diagnostic = Diagnostic::with('questions', 'tenants')->findOrFail($id);

        if ($user->role !== 'admin' || !$diagnostic->tenants->contains('id', $user->tenant_id)) {
            abort(403, 'Você não tem permissão para acessar esse diagnóstico.');
        }

        $alreadyAnswered = Answer::where('diagnostic_id', $diagnostic->id)
            ->where('tenant_id', $user->tenant_id)
            ->exists();

        if ($alreadyAnswered) {
            return redirect()->route('diagnostico.index')
                ->with('error', 'Este diagnóstico já foi respondido pela empresa.');
        }

        return view ('diagnostic.availables', compact('diagnostic'));
    }

    public function submitAnswer(Request $request, string $id) {
        $diagnostic = Diagnostic::with('questions')->findOrFail($id);
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

        $validQuestionIds = $diagnostic->questions->pluck('id')->toArray(); 

        foreach ($request->answers as $questionId => $note) {
            if (!in_array($questionId, $validQuestionIds)) {
                continue;
            }

            Answer::create([
                'user_id'       => $user->id,
                'diagnostic_id' => $diagnostic->id,
                'question_id'   => $questionId,
                'note'          => $note,
                'tenant_id'     => $user->tenant_id
            ]);
        }
        
        return redirect()->route('diagnostico.index')->with('success', 'Respostas enviadas com sucesso!');
    }
}
