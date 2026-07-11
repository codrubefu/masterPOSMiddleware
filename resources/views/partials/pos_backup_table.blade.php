@php
    $columns = [];
    foreach ($rows as $row) {
        $columns = array_values(array_unique(array_merge($columns, array_keys($row))));
    }
@endphp

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            @foreach ($columns as $column)
                <th>{{ $column }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach ($rows as $row)
            <tr>
                @foreach ($columns as $column)
                    <td>{{ $row[$column] ?? '' }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
