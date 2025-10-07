@extends('dashboard')

@section('title', 'Campanha')

@section('content')
    <div class="container-fluid campanha" id="create">
        <div class="row">
            <div class="col-12 py-5">
                <div class="header">
                    <h4>Criar campanha</h4>
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

                <form action="{{ route('campanha.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group mt-3">
                        <label for="title" class="form-label">Título</label>
                        <input type="text" class="form-control" name="title" id="title" value="{{ old('title') }}" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" name="description" id="description" rows="4">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group mt-3">
                        <label for="category_id" class="form-label">Categoria</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">Selecione uma categoria</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="active" class="form-label">Ativa?</label>
                        <select name="active" id="active" class="form-select">
                            <option value="1" {{ old('active', '1') == '1' ? 'selected' : '' }} selected>Sim</option>
                            <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Não</option>
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="company_id" class="form-label">Empresas (plano 3)</label>
                        <select name="company_ids[]" id="company_id" class="form-select" multiple required>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" {{ (collect(old('company_ids'))->contains($company->id)) ? 'selected' : '' }}>
                                    {{ $company->nome }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Segure Ctrl (Cmd no Mac) para selecionar múltiplas empresas</small>
                    </div>

                    <hr>
                    <h5>Conteúdos da Campanha</h5>
                    <div id="contents-wrapper">
                        <div class="content-item mb-3">
                            <select name="contents[0][type]" class="form-select mb-2">
                                <option value="text">Texto</option>
                                <option value="image">Imagem</option>
                                <option value="video">Vídeo</option>
                                <option value="pdf">PDF</option>
                                <option value="link">Link</option>
                            </select>
                            <textarea name="contents[0][content]" class="form-control mb-2" placeholder="Conteúdo ou descrição"></textarea>
                            <input type="file" name="contents[0][file]" class="form-control">
                        </div>
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

@push('scripts')
    <script>
        let index = 1;
        document.getElementById('add-content').addEventListener('click', () => {
            const wrapper = document.getElementById('contents-wrapper');
            const div = document.createElement('div');
            div.classList.add('content-item', 'mb-3');
            div.innerHTML = `
                <select name="contents[${index}][type]" class="form-select mb-2">
                    <option value="text">Texto</option>
                    <option value="image">Imagem</option>
                    <option value="video">Vídeo</option>
                    <option value="pdf">PDF</option>
                    <option value="link">Link</option>
                </select>
                <textarea name="contents[${index}][content]" class="form-control mb-2" placeholder="Conteúdo ou descrição"></textarea>
                <input type="file" name="contents[${index}][file]" class="form-control">
            `;
            wrapper.appendChild(div);
            index++;
        });
    </script>
@endpush