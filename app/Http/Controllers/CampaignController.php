<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Campaign;
use App\Models\Tenant;
use App\Models\CampaignContent;

class CampaignController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();

        if ($authUser->role === 'superadmin') {
            $campaigns = Campaign::with(['contents', 'tenants', 'category'])->get();
        } else {
            $campaigns = Campaign::with(['contents', 'tenants', 'category'])
                ->whereHas('tenants', fn($q) => $q->where('id', $authUser->tenant_id))
                ->get();
        }

        return view ('campaign.index', compact('campaigns'));
    }

    public function create()
    {
        $companies = Tenant::where('plain_id', 3)->get();
        $categories = Category::all();
        return view ('campaign.create', compact('companies', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'category_id'          => 'nullable|exists:categories,id',
            'company_ids'          => 'required|array',
            'company_ids.*'        => 'exists:tenants,id',
            'active'               => 'boolean',
            'contents.*.type'      => 'required|string|in:text,image,video,pdf,link',
            'contents.*.content'   => 'nullable|string',
            'contents.*.file'      => 'nullable|file|max:10240'
        ], [
            'title.required'           => 'O título da campanha é obrigatório.',
            'category_id.exists'       => 'A categoria selecionada não é válida.',
            'contents.*.type.required' => 'O tipo de conteúdo é obrigatório.',
            'contents.*.type.in'       => 'O tipo de conteúdo deve ser texto, imagem, vídeo, pdf ou link.',
            'contents.*.file.file'     => 'O arquivo enviado deve ser válido.',
            'contents.*.file.max'      => 'O arquivo não pode ultrapassar 10MB.',
        ]);

        $companies = Tenant::whereIn('id', $request->company_ids)
            ->where('plain_id', 3)
            ->get();

        if ($companies->isEmpty()) {
            return back()->withErrors([
                'company_ids' => 'Nenhuma empresa válida selecionada ou empresas não estão no plano 3.'
            ])->withInput();
        }

        $campaign = Campaign::create([
            'title'       => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'active'      => $request->active
        ]);

        $campaign->tenants()->attach($companies->pluck('id')->toArray());
        
        if ($request->has('contents')) {
            foreach ($request->contents as $content) {
                $filePath = $content['file'] ?? false ? $content['file']->store('campaigns') : null;

                $campaign->contents()->create([
                    'type'      => $content['type'],
                    'content'   => $content['content'] ?? null,
                    'file_path' => $filePath
                ]);
            }
        }

        return redirect()->route('campanha.index')->with('success', 'Campanha criada com sucesso!');
    }
}
