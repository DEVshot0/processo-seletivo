@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Adicionar Cliente</h1>

    <form id="form-add-cliente" class="mb-4">
        @csrf
        <div class="form-group">
            <label for="nome_completo">Nome Completo</label>
            <input type="text" name="nome_completo" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="cpf">CPF</label>
            <input type="text" name="cpf" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="data_nascimento">Data de Nascimento</label>
            <input type="date" name="data_nascimento" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="rua">Rua</label>
            <input type="text" name="rua" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="bairro">Bairro</label>
            <input type="text" name="bairro" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="cidade">Cidade</label>
            <input type="text" name="cidade" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="estado">Estado</label>
            <input type="text" name="estado" class="form-control" maxlength="2" required>
        </div>
        <div class="form-group">
            <label for="complemento">Complemento</label>
            <input type="text" name="complemento" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Salvar Cliente</button>
    </form>

    <hr>

    <h2>Lista de Clientes</h2>
    <ul id="lista-clientes" class="list-group">
        @foreach($clientes as $cliente)
            <li class="list-group-item" id="cliente-{{ $cliente->id }}">
                {{ $cliente->nome_completo }} - {{ $cliente->cpf }}
                <button class="btn btn-sm btn-danger float-right" onclick="excluirCliente({{ $cliente->id }})">Excluir</button>
                <button class="btn btn-sm btn-warning float-right mr-2" onclick="editarCliente({{ $cliente->id }}, '{{ $cliente->nome_completo }}', '{{ $cliente->cpf }}', '{{ $cliente->data_nascimento }}', '{{ $cliente->rua }}', '{{ $cliente->bairro }}', '{{ $cliente->cidade }}', '{{ $cliente->estado }}', '{{ $cliente->complemento }}')">Editar</button>
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
    document.getElementById('form-add-cliente').addEventListener('submit', function (e) {
        e.preventDefault();

        let form = new FormData(this);

        fetch('{{ route('clientes.store') }}', {
            method: 'POST',
            body: form,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao salvar o cliente.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                let cliente = data.cliente;
                let li = document.createElement('li');
                li.classList.add('list-group-item');
                li.setAttribute('id', `cliente-${cliente.id}`);
                li.innerHTML = `${cliente.nome_completo} - ${cliente.cpf}
                    <button class="btn btn-sm btn-danger float-right" onclick="excluirCliente(${cliente.id})">Excluir</button>
                    <button class="btn btn-sm btn-warning float-right mr-2" onclick="editarCliente(${cliente.id}, '${cliente.nome_completo}', '${cliente.cpf}', '${cliente.data_nascimento}', '${cliente.rua}', '${cliente.bairro}', '${cliente.cidade}', '${cliente.estado}', '${cliente.complemento}')">Editar</button>`;
                document.getElementById('lista-clientes').appendChild(li);

                document.querySelector('[name="nome_completo"]').value = '';
                document.querySelector('[name="cpf"]').value = '';
                document.querySelector('[name="data_nascimento"]').value = '';
                document.querySelector('[name="rua"]').value = '';
                document.querySelector('[name="bairro"]').value = '';
                document.querySelector('[name="cidade"]').value = '';
                document.querySelector('[name="estado"]').value = '';
                document.querySelector('[name="complemento"]').value = '';

                document.getElementById('success-message').style.display = 'block';
                document.getElementById('success-message').innerText = 'Cliente criado com sucesso.';
                document.getElementById('error-message').style.display = 'none';
            } else {
                document.getElementById('error-message').style.display = 'block';
                document.getElementById('error-message').innerText = data.message || 'Erro ao salvar o cliente.';
                document.getElementById('success-message').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('error-message').style.display = 'block';
            document.getElementById('error-message').innerText = 'Erro ao salvar o cliente.';
            document.getElementById('success-message').style.display = 'none';
        });
    });

    function excluirCliente(id) {
        if (!confirm('Tem certeza que deseja excluir este cliente?')) {
            return;
        }

        fetch(`/clientes/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao excluir o cliente.');
            }
            document.getElementById(`cliente-${id}`).remove();

            document.getElementById('success-message').style.display = 'block';
            document.getElementById('success-message').innerText = 'Cliente excluído com sucesso.';
            document.getElementById('error-message').style.display = 'none';
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('error-message').style.display = 'block';
            document.getElementById('error-message').innerText = 'Erro ao excluir o cliente.';
            document.getElementById('success-message').style.display = 'none';
        });
    }

    function editarCliente(id, nomeAtual, cpfAtual, dataAtual, ruaAtual, bairroAtual, cidadeAtual, estadoAtual, complementoAtual) {
        const nome_completo = prompt('Digite o novo nome do cliente:', nomeAtual);
        const cpf = prompt('Digite o novo CPF do cliente:', cpfAtual);
        const data_nascimento = prompt('Digite a nova data de nascimento do cliente:', dataAtual);
        const rua = prompt('Digite a nova rua do cliente:', ruaAtual);
        const bairro = prompt('Digite o novo bairro do cliente:', bairroAtual);
        const cidade = prompt('Digite a nova cidade do cliente:', cidadeAtual);
        const estado = prompt('Digite o novo estado do cliente:', estadoAtual);
        const complemento = prompt('Digite o novo complemento do cliente (opcional):', complementoAtual);

        if (nome_completo && cpf && data_nascimento && rua && bairro && cidade && estado) {
            fetch(`/clientes/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nome_completo: nome_completo,
                    cpf: cpf,
                    data_nascimento: data_nascimento,
                    rua: rua,
                    bairro: bairro,
                    cidade: cidade,
                    estado: estado,
                    complemento: complemento
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao editar o cliente.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const li = document.getElementById(`cliente-${id}`);
                    li.innerHTML = `${data.cliente.nome_completo} - ${data.cliente.cpf}
                        <button class="btn btn-sm btn-danger float-right" onclick="excluirCliente(${data.cliente.id})">Excluir</button>
                        <button class="btn btn-sm btn-warning float-right mr-2" onclick="editarCliente(${data.cliente.id}, '${data.cliente.nome_completo}', '${data.cliente.cpf}', '${data.cliente.data_nascimento}', '${data.cliente.rua}', '${data.cliente.bairro}', '${data.cliente.cidade}', '${data.cliente.estado}', '${data.cliente.complemento}')">Editar</button>`;

                    document.getElementById('success-message').style.display = 'block';
                    document.getElementById('success-message').innerText = 'Cliente atualizado com sucesso.';
                    document.getElementById('error-message').style.display = 'none';
                } else {
                    document.getElementById('error-message').style.display = 'block';
                    document.getElementById('error-message').innerText = data.message || 'Erro ao atualizar o cliente.';
                    document.getElementById('success-message').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('error-message').style.display = 'block';
                document.getElementById('error-message').innerText = 'Erro ao atualizar o cliente.';
                document.getElementById('success-message').style.display = 'none';
            });
        }
    }
</script>
@endsection
