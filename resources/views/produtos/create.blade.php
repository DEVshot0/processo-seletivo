@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Adicionar Produto</h1>

    <form id="form-add-produto">
        @csrf
        <div class="form-group">
            <label for="nome">Nome do Produto</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="valor_unitario">Valor Unitário</label>
            <input type="number" name="valor_unitario" class="form-control" step="0.01" required>
        </div>
        <button type="submit" class="btn btn-primary">Salvar Produto</button>
    </form>

    <hr>

    <h2>Lista de Produtos</h2>
    <ul id="lista-produtos" class="list-group">
        @foreach($produtos as $produto)
            <li class="list-group-item" id="produto-{{ $produto->id }}">
                {{ $produto->nome }} - R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                <button class="btn btn-sm btn-danger float-right" onclick="excluirProduto({{ $produto->id }})">Excluir</button>
                <button class="btn btn-sm btn-warning float-right mr-2" onclick="editarProduto({{ $produto->id }}, '{{ $produto->nome }}', {{ $produto->valor_unitario }})">Editar</button>
            </li>
        @endforeach
    </ul>

    <div id="error-message" class="alert alert-danger mt-3" style="display: none;">
        Ocorreu um erro ao processar a solicitação.
    </div>

    <div id="success-message" class="alert alert-success mt-3" style="display: none;">
        Operação realizada com sucesso.
    </div>
</div>

<script>
    document.getElementById('form-add-produto').addEventListener('submit', function (e) {
        e.preventDefault(); 

        let form = new FormData(this);

        fetch('{{ route('produtos.store') }}', {
            method: 'POST',
            body: form,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao salvar o produto.');
            }
            return response.json(); 
        })
        .then(data => {
            if (data.success) {
                let produto = data.produto;
                let li = document.createElement('li');
                li.classList.add('list-group-item');
                li.setAttribute('id', `produto-${produto.id}`);
                li.innerHTML = `${produto.nome} - R$ ${parseFloat(produto.valor_unitario).toFixed(2)}
                    <button class="btn btn-sm btn-danger float-right" onclick="excluirProduto(${produto.id})">Excluir</button>
                    <button class="btn btn-sm btn-warning float-right mr-2" onclick="editarProduto(${produto.id}, '${produto.nome}', ${produto.valor_unitario})">Editar</button>`;
                document.getElementById('lista-produtos').appendChild(li);

                document.querySelector('[name="nome"]').value = '';
                document.querySelector('[name="valor_unitario"]').value = '';

                document.getElementById('success-message').style.display = 'block';
                document.getElementById('success-message').innerText = 'Produto criado com sucesso.';
                document.getElementById('error-message').style.display = 'none';
            } else {
                document.getElementById('error-message').style.display = 'block';
                document.getElementById('error-message').innerText = data.message || 'Erro ao salvar o produto.';
                document.getElementById('success-message').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('error-message').style.display = 'block';
            document.getElementById('error-message').innerText = 'Erro ao salvar o produto.';
            document.getElementById('success-message').style.display = 'none';
        });
    });

    function excluirProduto(id) {
        if (!confirm('Tem certeza que deseja excluir este produto?')) {
            return;
        }

        fetch(`/produtos/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao excluir o produto.');
            }
            document.getElementById(`produto-${id}`).remove();

            document.getElementById('success-message').style.display = 'block';
            document.getElementById('success-message').innerText = 'Produto excluído com sucesso.';
            document.getElementById('error-message').style.display = 'none';
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('error-message').style.display = 'block';
            document.getElementById('error-message').innerText = 'Erro ao excluir o produto.';
            document.getElementById('success-message').style.display = 'none';
        });
    }

    function editarProduto(id, nomeAtual, valorAtual) {
        const nome = prompt('Digite o novo nome do produto:', nomeAtual);
        const valor = prompt('Digite o novo valor do produto:', valorAtual);

        if (nome && valor) {
            fetch(`/produtos/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nome: nome,
                    valor_unitario: parseFloat(valor)
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao editar o produto.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const li = document.getElementById(`produto-${id}`);
                    li.innerHTML = `${data.produto.nome} - R$ ${parseFloat(data.produto.valor_unitario).toFixed(2)}
                        <button class="btn btn-sm btn-danger float-right" onclick="excluirProduto(${data.produto.id})">Excluir</button>
                        <button class="btn btn-sm btn-warning float-right mr-2" onclick="editarProduto(${data.produto.id}, '${data.produto.nome}', ${data.produto.valor_unitario})">Editar</button>`;

                    document.getElementById('success-message').style.display = 'block';
                    document.getElementById('success-message').innerText = 'Produto atualizado com sucesso.';
                    document.getElementById('error-message').style.display = 'none';
                } else {
                    document.getElementById('error-message').style.display = 'block';
                    document.getElementById('error-message').innerText = data.message || 'Erro ao atualizar o produto.';
                    document.getElementById('success-message').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('error-message').style.display = 'block';
                document.getElementById('error-message').innerText = 'Erro ao atualizar o produto.';
                document.getElementById('success-message').style.display = 'none';
            });
        }
    }
</script>
@endsection
