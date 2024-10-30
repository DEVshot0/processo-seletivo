@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nova Compra</h1>

    <form id="form-add-compra" action="{{ route('compras.store') }}" method="POST" onsubmit="return validarCompra()">
        @csrf

        <div class="form-group">
            <label for="cliente_id">Cliente</label>
            <select name="cliente_id" id="cliente_id" class="form-control" required>
                <option value="">Selecione um Cliente</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->nome_completo }}</option>
                @endforeach
            </select>
        </div>

        <div id="produtos-container">
            <h4>Produtos</h4>
            <div class="form-group produto-item">
                <label for="produto_id">Produto</label>
                <select name="produtos[0][produto_id]" class="form-control produto-select" required onchange="atualizarPrecoTotal()">
                    <option value="">Selecione um Produto</option>
                    @foreach($produtos as $produto)
                        <option value="{{ $produto->id }}" data-valor="{{ $produto->valor_unitario }}">
                            {{ $produto->nome }} - R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                        </option>
                    @endforeach
                </select>
                <label for="quantidade">Quantidade</label>
                <input type="number" name="produtos[0][quantidade]" class="form-control quantidade-input" min="1" value="1" required onchange="atualizarPrecoTotal()">
            </div>
        </div>

        <button type="button" class="btn btn-success mb-3" onclick="adicionarProduto()">Adicionar Produto</button>

        <div class="form-group">
            <label>Total da Compra:</label>
            <input type="text" id="total-compra" class="form-control" value="R$ 0,00" readonly>
        </div>

        <div class="form-group">
            <label>Forma de Pagamento</label>
            <div>
                <input type="radio" name="pagamento" value="avista" id="avista" onclick="exibirParcelamento(false)" checked>
                <label for="avista">À Vista</label>

                <input type="radio" name="pagamento" value="parcelado" id="parcelado" onclick="exibirParcelamento(true)">
                <label for="parcelado">Parcelado</label>
            </div>
        </div>

        <div id="parcelamento" style="display:none;">
            <div class="form-group">
                <label for="num_parcelas">Número de Parcelas</label>
                <input type="number" id="num_parcelas" name="num_parcelas" class="form-control" min="1" value="1">
            </div>
            <button type="button" class="btn btn-secondary mb-3" onclick="simularParcelas()">Simular Parcelas</button>
            <div id="parcelas"></div>
        </div>

        <button type="submit" class="btn btn-primary">Finalizar Compra</button>
    </form>

    <h2 class="mt-5">Listagem de Compras</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Valor Total</th>
                <th>Forma de Pagamento</th>
                <th>Número de Parcelas</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compras as $compra)
                <tr>
                    <td>{{ $compra->id }}</td>
                    <td>{{ $compra->cliente->nome_completo }}</td>
                    <td>R$ {{ number_format($compra->valor_total, 2, ',', '.') }}</td>
                    <td>{{ ucfirst($compra->forma_pagamento) }}</td>
                    <td>
                        {{ $compra->forma_pagamento == 'parcelado' ? $compra->parcelas->count() : 0 }}
                        @if($compra->forma_pagamento == 'parcelado')
                            <button type="button" class="btn btn-info btn-sm" onclick="mostrarParcelasModal({{ $compra->id }})">Ver Parcelas</button>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('compras.edit', $compra->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('compras.destroy', $compra->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta compra?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>


<div class="modal fade" id="parcelasModal" tabindex="-1" aria-labelledby="parcelasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="parcelasModalLabel">Valores das Parcelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-parcelas-content">
                <p>Nenhuma parcela disponível.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
function exibirParcelamento(exibir) {
    document.getElementById('parcelamento').style.display = exibir ? 'block' : 'none';
}

let produtoIndex = 1;
function adicionarProduto() {
    const container = document.getElementById('produtos-container');
    const produtoHTML = `
        <div class="form-group produto-item">
            <label for="produto_id">Produto</label>
            <select name="produtos[${produtoIndex}][produto_id]" class="form-control produto-select" required onchange="atualizarPrecoTotal()">
                <option value="">Selecione um Produto</option>
                @foreach($produtos as $produto)
                    <option value="{{ $produto->id }}" data-valor="{{ $produto->valor_unitario }}">
                        {{ $produto->nome }} - R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                    </option>
                @endforeach
            </select>
            <label for="quantidade">Quantidade</label>
            <input type="number" name="produtos[${produtoIndex}][quantidade]" class="form-control quantidade-input" min="1" value="1" required onchange="atualizarPrecoTotal()">
        </div>
    `;
    container.insertAdjacentHTML('beforeend', produtoHTML);
    produtoIndex++;
    atualizarPrecoTotal();
}

function atualizarPrecoTotal() {
    let total = 0;
    document.querySelectorAll('.produto-item').forEach(item => {
        const produto = item.querySelector('.produto-select');
        const quantidade = item.querySelector('.quantidade-input').value;
        const valorUnitario = produto.options[produto.selectedIndex].getAttribute('data-valor') || 0;
        total += valorUnitario * quantidade;
    });
    document.getElementById('total-compra').value = 'R$ ' + total.toFixed(2).replace('.', ',');
}

function simularParcelas() {
    const numParcelas = document.getElementById('num_parcelas').value;
    const parcelasDiv = document.getElementById('parcelas');
    parcelasDiv.innerHTML = '';

    const valorTotal = parseFloat(document.getElementById('total-compra').value.replace('R$', '').replace(',', '.'));
    const valorParcela = (valorTotal / numParcelas).toFixed(2);
    let dataAtual = new Date();

    for (let i = 1; i <= numParcelas; i++) {
        dataAtual.setMonth(dataAtual.getMonth() + 1);
        const dataVencimento = dataAtual.toISOString().split('T')[0];

        parcelasDiv.innerHTML += `
            <div class="form-group">
                <label>Parcela ${i} - Valor:</label>
                <input type="number" name="parcelas[${i}][valor]" class="form-control" value="${valorParcela}" step="0.01" required>
                <label>Data de Vencimento:</label>
                <input type="date" name="parcelas[${i}][data_vencimento]" class="form-control" value="${dataVencimento}" required>
            </div>
        `;
    }
}

function validarCompra() {
    const numParcelas = document.getElementById('num_parcelas').value;
    const valorTotal = parseFloat(document.getElementById('total-compra').value.replace('R$', '').replace(',', '.'));

    if (document.querySelector('input[name="pagamento"]:checked').value === 'parcelado') {
        const somaParcelas = Array.from(document.querySelectorAll('input[name^="parcelas["][name$="[valor]"]')).reduce((total, input) => {
            const valorParcela = parseFloat(input.value);
            if (valorParcela <= 0) {
                alert('O valor da parcela deve ser maior que zero.');
                return false; 
            }
            return total + valorParcela;
        }, 0);

        if (somaParcelas !== valorTotal) {
            alert('A soma das parcelas deve ser igual ao valor total da compra. Verifique os valores das parcelas.');
            return false; 
        }
    }

    return true;
}

function mostrarParcelasModal(compraId) {
    const modalContent = document.getElementById('modal-parcelas-content');
    modalContent.innerHTML = 'Carregando...';

    fetch(`/compras/${compraId}/parcelas`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.parcelas.length > 0) {
                modalContent.innerHTML = data.parcelas.map((parcela, index) => `
                    <p>Parcela ${index + 1}: R$ ${parcela.valor_parcela} - Vencimento: ${parcela.data_vencimento}</p>
                `).join('');
            } else {
                modalContent.innerHTML = '<p>Nenhuma parcela disponível.</p>';
            }
        })
        .catch(error => {
            modalContent.innerHTML = '<p>Erro ao carregar as parcelas. Tente novamente.</p>';
            console.error('There was a problem with the fetch operation:', error);
        });

    const myModal = new bootstrap.Modal(document.getElementById('parcelasModal'));
    myModal.show();
}
</script>
@endsection
