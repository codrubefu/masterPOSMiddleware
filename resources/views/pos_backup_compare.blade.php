<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comparare backup POS</title>
    <style>
        body {
            margin: 0;
            background: #f4f6f8;
            color: #17202a;
            font-family: Arial, Helvetica, sans-serif;
        }

        .page {
            max-width: 1280px;
            margin: 0 auto;
            padding: 28px;
        }

        h1 {
            margin: 0 0 18px;
            font-size: 26px;
        }

        form {
            display: flex;
            align-items: end;
            gap: 12px;
            margin-bottom: 22px;
            padding: 16px;
            background: #ffffff;
            border: 1px solid #d9e0e7;
            border-radius: 8px;
        }

        label {
            display: grid;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
        }

        input, button {
            height: 38px;
            border-radius: 6px;
            font-size: 14px;
        }

        input {
            padding: 0 10px;
            border: 1px solid #bac6d3;
        }

        button {
            padding: 0 16px;
            border: 0;
            background: #1f6feb;
            color: #ffffff;
            font-weight: 700;
        }

        .note {
            margin: 0 0 18px;
            color: #4d5b6a;
            font-size: 14px;
        }

        .section {
            margin-top: 18px;
            padding: 18px;
            background: #ffffff;
            border: 1px solid #d9e0e7;
            border-radius: 8px;
        }

        .section-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        h2 {
            margin: 0;
            font-size: 18px;
        }

        .summary {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .badge {
            padding: 5px 9px;
            border-radius: 999px;
            background: #eef2f6;
            font-size: 13px;
            font-weight: 700;
        }

        .badge.error {
            background: #fde8e8;
            color: #9b1c1c;
        }

        h3 {
            margin: 16px 0 8px;
            font-size: 15px;
        }

        .empty {
            margin: 10px 0 0;
            color: #1f7a4d;
            font-weight: 700;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid #e2e8ef;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            white-space: nowrap;
        }

        th, td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8ef;
            text-align: left;
        }

        th {
            position: sticky;
            top: 0;
            background: #eef2f6;
            color: #344253;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        @media (max-width: 700px) {
            .page {
                padding: 16px;
            }

            form {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<main class="page">
    <h1>Comparare backup POS</h1>

    <form method="GET" action="{{ route('pos-backup-compare.index') }}">
        <label>
            Ziua
            <input type="date" name="date" value="{{ $date }}">
        </label>
        <button type="submit">Verifica</button>
    </form>

    <p class="note">
        Campurile de data sunt egale daca diferenta este de maxim {{ $toleranceMinutes }} minute.
        La detalii se ignora <strong>nrbonf</strong>. Pentru cheile generate se ignora <strong>nrbonfint</strong> si <strong>idtrzf</strong>.
    </p>

    @include('partials.pos_backup_comparison', [
        'title' => 'TrzCfePOS vs TrzCfePOSSent',
        'mainLabel' => 'TrzCfePOS',
        'backupLabel' => 'TrzCfePOSSent',
        'comparison' => $headerComparison,
    ])

    @include('partials.pos_backup_comparison', [
        'title' => 'TrzdetcfPOS vs TrzdetcfPOSSent',
        'mainLabel' => 'TrzdetcfPOS',
        'backupLabel' => 'TrzdetcfPOSSent',
        'comparison' => $detailComparison,
    ])
</main>
</body>
</html>
