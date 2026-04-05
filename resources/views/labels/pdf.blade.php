<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Etichete produse</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .header { margin-bottom: 14px; }
        .labels { width: 100%; border-collapse: collapse; }
        .labels td { width: 50%; border: 1px solid #d1d5db; padding: 10px; vertical-align: top; }
        .product-name { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
        .price { font-size: 18px; font-weight: bold; margin-top: 8px; }
        .meta { color: #4b5563; }
    </style>
</head>
<body>
<div class="header">
    <h2>Etichete produse</h2>
    <p>Generat la: {{ $generatedAt->format('d.m.Y H:i') }}</p>
</div>

<table class="labels">
    <tbody>
    @php
        $expanded = [];
        foreach ($labels as $label) {
            for ($i = 0; $i < $label['quantity']; $i++) {
                $expanded[] = $label;
            }
        }
    @endphp

    @foreach(array_chunk($expanded, 2) as $row)
        <tr>
            @foreach($row as $item)
                <td>
                    <div class="product-name">{{ $item['name'] }}</div>
                    <div class="meta">Cod: {{ $item['sku'] }}</div>
                    <div class="price">{{ number_format($item['price'], 2, ',', '.') }} lei</div>
                </td>
            @endforeach
            @if(count($row) < 2)
                <td></td>
            @endif
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
