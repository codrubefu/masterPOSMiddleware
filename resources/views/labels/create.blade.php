@extends('layouts.app')

@section('title', 'Generator etichete produse')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h3 mb-3">Generator etichete (demo)</h1>
            <p class="text-muted">Selectează produse, adaugă-le în listă și descarcă etichetele în format PDF.</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="productSelect">Produs</label>
                    <select id="productSelect" class="form-select">
                        @foreach($products as $product)
                            <option value="{{ $product['id'] }}" data-name="{{ $product['name'] }}" data-price="{{ $product['price'] }}" data-sku="{{ $product['sku'] }}">
                                {{ $product['name'] }} ({{ number_format($product['price'], 2, ',', '.') }} lei)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="quantityInput">Cantitate</label>
                    <input type="number" id="quantityInput" class="form-control" min="1" value="1">
                </div>
                <div class="col-md-4">
                    <button type="button" id="addProductBtn" class="btn btn-primary">Adaugă în listă</button>
                </div>
            </div>

            <form method="POST" action="{{ route('labels.store') }}" id="labelForm">
                @csrf
                <input type="hidden" name="selected_products" id="selectedProductsInput" value="[]">

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="selectedProductsTable">
                        <thead class="table-light">
                        <tr>
                            <th>Produs</th>
                            <th>Cod</th>
                            <th class="text-end">Preț</th>
                            <th class="text-center">Cantitate etichete</th>
                            <th class="text-center">Acțiune</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr id="emptyRow">
                            <td colspan="5" class="text-center text-muted">Nu ai adăugat produse.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-success">Salvează PDF</button>
            </form>
        </div>
    </div>

    <script>
        const selected = new Map();
        const productSelect = document.getElementById('productSelect');
        const quantityInput = document.getElementById('quantityInput');
        const addProductBtn = document.getElementById('addProductBtn');
        const tableBody = document.querySelector('#selectedProductsTable tbody');
        const hiddenInput = document.getElementById('selectedProductsInput');

        function syncHiddenInput() {
            hiddenInput.value = JSON.stringify(Array.from(selected.values()));
        }

        function renderTable() {
            tableBody.innerHTML = '';

            if (!selected.size) {
                tableBody.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center text-muted">Nu ai adăugat produse.</td></tr>';
                syncHiddenInput();
                return;
            }

            selected.forEach((item, id) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td>${item.sku}</td>
                    <td class="text-end">${Number(item.price).toFixed(2)} lei</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" data-remove="${id}">Șterge</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });

            syncHiddenInput();
        }

        addProductBtn.addEventListener('click', () => {
            const option = productSelect.selectedOptions[0];
            const id = Number(option.value);
            const quantity = Number(quantityInput.value || 1);

            if (quantity <= 0) {
                return;
            }

            const existing = selected.get(id);
            selected.set(id, {
                id,
                name: option.dataset.name,
                sku: option.dataset.sku,
                price: option.dataset.price,
                quantity: existing ? existing.quantity + quantity : quantity,
            });

            renderTable();
        });

        tableBody.addEventListener('click', (event) => {
            const btn = event.target.closest('button[data-remove]');
            if (!btn) {
                return;
            }
            selected.delete(Number(btn.dataset.remove));
            renderTable();
        });

        renderTable();
    </script>
@endsection
