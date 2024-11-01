@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Compra</h1>

    <form id="form-edit-compra" action="{{ route('compras.update', $compra->id) }}" method="POST" onsubmit="return validarCompra()">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="cliente_id">Cliente</label>
            <select name="cliente_id" id="cliente_id" class="form-control" required>
                <option value="">Selecione um Cliente</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" {{ $compra->cliente_id == $cliente->id ? 'selected' : '' }}>
                        {{ $cliente->nome_completo }}
                    </option>
                @endforeach
            </select>
        </div>

        <div id="produtos-container">
            <h4>Produtos</h4>
            @foreach($compra->produtos as $index => $produto)
                <div class="form-group produto-item">
                    <label for="produto_id">Produto</label>
                    <select name="produtos[{{ $index }}][produto_id]" class="form-control produto-select" required onchange="atualizarPrecoTotal()">
                        <option value="">Selecione um Produto</option>
                        @foreach($produtos as $p)
                            <option value="{{ $p->id }}" data-valor="{{ $p->valor_unitario }}" {{ $produto->id == $p->id ? 'selected' : '' }}>
                                {{ $p->nome }} - R$ {{ number_format($p->valor_unitario, 2, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    <label for="quantidade">Quantidade</label>
                    <input type="number" name="produtos[{{ $index }}][quantidade]" class="form-control quantidade-input" min="1" value="{{ $produto->pivot->quantidade }}" required onchange="atualizarPrecoTotal()">
                </div>
            @endforeach
        </div>

        <button type="button" class="btn btn-success mb-3" onclick="adicionarProduto()">Adicionar Produto</button>

        <div class="form-group">
            <label>Total da Compra:</label>
            <input type="text" id="total-compra" class="form-control" value="R$ {{ number_format($compra->valor_total, 2, ',', '.') }}" readonly>
        </div>

        <div class="form-group">
            <label>Forma de Pagamento</label>
            <div>
                <input type="radio" name="pagamento" value="avista" id="avista" onclick="exibirParcelamento(false)" {{ $compra->forma_pagamento == 'avista' ? 'checked' : '' }}>
                <label for="avista">À Vista</label>

                <input type="radio" name="pagamento" value="parcelado" id="parcelado" onclick="exibirParcelamento(true)" {{ $compra->forma_pagamento == 'parcelado' ? 'checked' : '' }}>
                <label for="parcelado">Parcelado</label>
            </div>
        </div>

        <div id="parcelamento" style="display: {{ $compra->forma_pagamento == 'parcelado' ? 'block' : 'none' }}">
            <div class="form-group">
                <label for="num_parcelas">Número de Parcelas</label>
                <input type="number" id="num_parcelas" name="num_parcelas" class="form-control" min="1" value="{{ $compra->parcelas->count() }}" readonly>
            </div>

            <div id="parcelas">
                @foreach($compra->parcelas as $index => $parcela)
                    <div class="form-group">
                        <label>Parcela {{ $index + 1 }} - Valor:</label>
                        <input type="number" name="parcelas[{{ $index }}][valor]" class="form-control" value="{{ $parcela->valor_parcela }}" step="0.01" required>
                        <label>Data de Vencimento:</label>
                        <input type="date" name="parcelas[{{ $index }}][data_vencimento]" class="form-control" value="{{ $parcela->data_vencimento }}" required>
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-secondary mb-3" onclick="adicionarParcela()">Adicionar Parcela</button>
        </div>

        <button type="submit" class="btn btn-primary">Atualizar Compra</button>
    </form>

    <h2 class="mt-5">Listagem de Compras</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Valor Total</th>
                <th>Forma de Pagamento</th>
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

<script>
function exibirParcelamento(exibir) {
    document.getElementById('parcelamento').style.display = exibir ? 'block' : 'none';
}

let produtoIndex = {{ $compra->produtos->count() }};
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

let parcelaIndex = {{ $compra->parcelas->count() }};
function adicionarParcela() {
    const parcelasContainer = document.getElementById('parcelas');
    const ultimaParcela = document.querySelector(`#parcelas input[name="parcelas[${parcelaIndex - 1}][data_vencimento]"]`);
    let novaData = new Date();
    if (ultimaParcela) {
        novaData = new Date(ultimaParcela.value);
        novaData.setMonth(novaData.getMonth() + 1);
    }
    const dataFormatada = novaData.toISOString().split('T')[0];
    const parcelaHTML = `
        <div class="form-group">
            <label>Parcela ${parcelaIndex + 1} - Valor:</label>
            <input type="number" name="parcelas[${parcelaIndex}][valor]" class="form-control" value="0.00" step="0.01" required>
            <label>Data de Vencimento:</label>
            <input type="date" name="parcelas[${parcelaIndex}][data_vencimento]" class="form-control" value="${dataFormatada}" required>
        </div>
    `;
    parcelasContainer.insertAdjacentHTML('beforeend', parcelaHTML);
    parcelaIndex++;
}

function validarCompra() {
    const valorTotal = parseFloat(document.getElementById('total-compra').value.replace('R$', '').replace(',', '.'));
    if (document.querySelector('input[name="pagamento"]:checked').value === 'parcelado') {
        const somaParcelas = Array.from(document.querySelectorAll('input[name^="parcelas["][name$="[valor]"]')).reduce((total, input) => {
            const valorParcela = parseFloat(input.value.replace(',', '.'));
            if (valorParcela <= 0) {
                alert('O valor da parcela deve ser diferente de zero.');
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
</script>
@endsection
