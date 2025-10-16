@extends('dashboard')

@section('title', 'Campanha')

@section('content')
    <div class="container-fluid campanha" id="edit">
        <div class="row">
            <div class="col-12 py-5">
                <div class="header">
                    <h4>Editar campanha '{{ $campaign->title }}'</h4>
                    <a href="{{ route('campanha.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                <form action="{{ route('campanha.update', $campaign->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group mt-3">
                        <label for="title" class="form-label">Título</label>
                        <input type="text" class="form-control" name="title" id="title" value="{{ old('title', $campaign->title) }}" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" name="description" id="description" rows="4">{{ old('description', $campaign->description) }}</textarea>
                    </div>

                    <div class="form-group mt-3">
                        <label for="category_id" class="form-label">Categoria</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">Selecione uma categoria</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $campaign->category_id === $category->id ? 'selected' : '') }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="active" class="form-label">Ativa?</label>
                        <select name="active" id="active" class="form-select">
                            <option value="1" {{ old('active', $campaign->active === 1 ? 'selected' : '') }}>Sim</option>
                            <option value="0" {{ old('active', $campaign->active === 0 ? 'selected' : '') }}>Não</option>
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="company_id" class="form-label">Empresas (plano 3)</label>
                        <select name="company_ids[]" id="company_id" class="form-select" multiple required>
                            @foreach ($companies as $company)
                                <option 
                                    value="{{ $company->id }}"
                                    {{ isset($campaign) && $campaign->tenants->pluck('id')->contains($company->id) ? 'selected' : '' }}
                                >
                                    {{ $company->nome }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Segure Ctrl (Cmd no Mac) para selecionar múltiplas empresas</small>
                    </div>

                    <hr>
                    <h5>Conteúdos da Campanha</h5>
                    <div id="contents-wrapper">
                        @foreach ($campaign->contents as $i => $content)
                            <div class="content-item mb-3 border p-3 rounded">
                                <select name="contents[{{ $i }}][type]" class="form-select mb-2" required>
                                    <option value="text" {{ $content->type === 'text' ? 'selected' : '' }}>Texto</option>
                                    <option value="image" {{ $content->type === 'image' ? 'selected' : '' }}>Imagem</option>
                                    <option value="video" {{ $content->type === 'video' ? 'selected' : '' }}>Vídeo</option>
                                    <option value="pdf" {{ $content->type === 'pdf' ? 'selected' : '' }}>PDF</option>
                                    <option value="link" {{ $content->type === 'link' ? 'selected' : '' }}>Link</option>
                                </select>
                                <textarea name="contents[{{ $i }}][content]" class="form-control mb-2" placeholder="Conteúdo ou descrição"></textarea>

                                @if ($content->file_path)
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Arquivo atual:</small>
                                        <a href="{{ asset('storage/' . $content->file_path) }}" target="_blank">Visualizar arquivo</a>
                                    </div>
                                @endif

                                <input type="file" name="contents[{{ $i }}][file]" class="form-control">
                                <button type="button" class="btn btn-sm btn-danger mt-2 remove-content d-block ms-auto">Remover</button>                                

                                <div class="video-compress-loader">
                                    <div class="loader-spinner"></div>
                                    <span>Compactando vídeo...</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" id="add-content" class="btn btn-sm btn-secondary mb-3">+ Adicionar Conteúdo</button>

                    <div>
                        <button type="submit" class="btn btn-primary">Salvar Campanha</button>
                        <a href="{{ route('campanha.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .content-item {
            position: relative;
        }
        .video-compress-loader {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.85);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            z-index: 10;
            display: none;
        }
        .loader-spinner {
            border: 3px solid #e9ecef;
            border-top: 3px solid #0d6efd;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endpush

@push('scripts')
    <script>
        let index = 1;

        async function compressVideo(file, loader) {
            return new Promise((resolve, reject) => {
                loader.style.display = 'flex';

                const video = document.createElement('video');
                video.src = URL.createObjectURL(file);
                video.muted = true;
                video.play();

                video.addEventListener('loadeddata', () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const maxWidth = 1280;
                    const scale = Math.min(1, maxWidth / video.videoWidth);

                    canvas.width = video.videoWidth * scale;
                    canvas.height = video.videoHeight * scale;

                    const stream = canvas.captureStream(25);
                    const recorder = new MediaRecorder(stream, {
                        mimeType: 'video/webm;codecs=vp9',
                        videoBitsPerSecond: 800000
                    });

                    const chunks = [];
                    recorder.ondataavailable = e => chunks.push(e.data);
                    recorder.onstop = () => {
                        const blob = new Blob(chunks, { type: 'video/webm' });
                        const compressedFile = new File([blob], 'compressed_' + file.name, { type: 'video/webm' });
                        loader.style.display = 'none';
                        resolve(compressedFile);
                    };

                    recorder.start();
                    const drawFrame = () => {
                        if (video.paused || video.ended) {
                            recorder.stop();
                            return;
                        }
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        requestAnimationFrame(drawFrame);
                    };
                    drawFrame();
                });

                video.onerror = err => {
                    loader.style.display = 'none';
                    reject(err);
                };
            });
        }

        document.getElementById('add-content').addEventListener('click', () => {
            const wrapper = document.getElementById('contents-wrapper');
            const div = document.createElement('div');
            div.classList.add('content-item', 'mb-3', 'border', 'p-3', 'rounded');

            div.innerHTML = `
                <select name="contents[${index}][type]" class="form-select mb-2" required>
                    <option value="text">Texto</option>
                    <option value="image">Imagem</option>
                    <option value="video">Vídeo</option>
                    <option value="pdf">PDF</option>
                    <option value="link">Link</option>
                </select>
                <textarea name="contents[${index}][content]" class="form-control mb-2" placeholder="Conteúdo ou descrição"></textarea>
                <input type="file" name="contents[${index}][file]" class="form-control">
                <button type="button" class="btn btn-sm btn-danger mt-2 remove-content d-block ms-auto">Remover</button>
                <div class="video-compress-loader">
                    <div class="loader-spinner"></div>
                    <span>Compactando vídeo...</span>
                </div>
            `;

            const fileInput = div.querySelector('input[type="file"]');
            const selectType = div.querySelector('select');
            const loader = div.querySelector('.video-compress-loader');

            fileInput.addEventListener('change', async (e) => {
                const file = e.target.files[0];
                if (selectType.value === 'video' && file) {
                    const compressed = await compressVideo(file, loader);
                    const dt = new DataTransfer();
                    dt.items.add(compressed);
                    e.target.files = dt.files;
                }
            });

            wrapper.appendChild(div);
            index++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-content')) {
                e.target.closest('.content-item').remove();
            }
        });

        document.querySelectorAll('#contents-wrapper input[type="file"]').forEach(input => {
            const selectType = input.closest('.content-item').querySelector('select');
            const loader = input.closest('.content-item').querySelector('.video-compress-loader');

            input.addEventListener('change', async (e) => {
                const file = e.target.files[0];
                if (selectType.value === 'video' && file) {
                    const compressed = await compressVideo(file, loader);
                    const dt = new DataTransfer();
                    dt.items.add(compressed);
                    e.target.files = dt.files;
                }
            });
        });
    </script>
@endpush