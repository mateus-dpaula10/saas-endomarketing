<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Campaign;
use App\Models\Tenant;
use App\Models\CampaignContent;
use Illuminate\Support\Facades\Storage;

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
            'category_id'          => 'required|exists:categories,id',
            'company_ids'          => 'required|array',
            'company_ids.*'        => 'exists:tenants,id',
            'active'               => 'boolean',
            'contents.*.type'      => 'required|string|in:text,image,video,pdf,link',
            'contents.*.content'   => 'nullable|string',
            'contents.*.file'      => 'nullable|file'
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
                $filePath = $content['file'] ?? false 
                    ? $content['file']->store('campaigns', 'public') 
                    : null;

                $campaign->contents()->create([
                    'type'      => $content['type'],
                    'content'   => $content['content'] ?? null,
                    'file_path' => $filePath
                ]);
            }
        }

        return redirect()->route('campanha.index')->with('success', 'Campanha criada com sucesso!');
    }

    public function edit(String $id)
    {
        $campaign = Campaign::with(['contents', 'tenants'])->findOrFail($id);
        $companies = Tenant::where('plain_id', 3)->get();
        $categories = Category::all();

        return view ('campaign.edit', compact('campaign', 'companies', 'categories'));
    }

    public function update(Request $request, String $id)
    {
        $campaign = Campaign::with(['contents', 'tenants'])->findOrFail($id);

        $request->validate([
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'category_id'          => 'required|exists:categories,id',
            'company_ids'          => 'required|array',
            'company_ids.*'        => 'exists:tenants,id',
            'active'               => 'boolean',
            'contents.*.type'      => 'required|string|in:text,image,video,pdf,link',
            'contents.*.content'   => 'nullable|string',
            'contents.*.file'      => 'nullable|file'
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

        $campaign->update([
            'title'       => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'active'      => $request->active
        ]);

        $campaign->tenants()->sync($companies->pluck('id')->toArray());

        $existingIds = $campaign->contents->pluck('id')->toArray();
        $keptIds = [];

        if ($request->has('contents')) {
            foreach ($request->contents as $index => $contentData) {
                if (isset($contentData['id']) && in_array($contentData['id'], $existingIds)) {
                    $content = $campaign->contents()->find($contentData['id']);

                    if (isset($contentData['file']) && $contentData['file'] instanceof \Illuminate\Http\UploadedFile) {
                        if ($content->file_path) {
                            Storage::disk('public')->delete($content->file_path);
                        }

                        $filePath = $contentData['file']->store('campaigns', 'public');
                    } else {
                        $filePath = $content->file_path;
                    }

                    $content->update([
                        'type'      => $contentData['type'],
                        'content'   => $contentData['content'] ?? null,
                        'file_path' => $filePath,
                    ]);

                    $keptIds[] = $content->id;
                } else {
                    $filePath = isset($contentData['file']) && $contentData['file'] instanceof \Illuminate\Http\UploadedFile
                        ? $contentData['file']->store('campaigns', 'public')
                        : null;

                    $newContent = $campaign->contents()->create([
                        'type'      => $contentData['type'],
                        'content'   => $contentData['content'] ?? null,
                        'file_path' => $filePath
                    ]);

                    $keptIds[] = $newContent->id;
                }
            }
        }

        $toDelete = array_diff($existingIds, $keptIds);
        foreach ($campaign->contents()->whereIn('id', $toDelete)->get() as $content) {
            if ($content->file_path) {
                Storage::disk('public')->delete($content->file_path);
            }
            $content->delete();
        }

        return redirect()->route('campanha.index')->with('success', 'Campanha atualizada com sucesso!');
    }

    public function destroy(String $id)
    {
        $campaign = Campaign::with(['contents', 'tenants'])->findOrFail($id);

        foreach ($campaign->contents as $content) {
            if ($content->file_path) {
                Storage::disk('public')->delete($content->file_path);
            }
        }

        $campaign->contents()->delete();

        $campaign->tenants()->detach();

        $campaign->delete();

        return redirect()->route('campanha.index')->with('success', 'Campanha excluída com sucesso!');
    }
}
