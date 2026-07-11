@php
    $missingFromBackup = $comparison['missing_from_backup'];
    $missingFromMain = $comparison['missing_from_main'];
    $hasDifferences = count($missingFromBackup) > 0 || count($missingFromMain) > 0;
@endphp

<section class="section">
    <div class="section-header">
        <h2>{{ $title }}</h2>
        <div class="summary">
            <span class="badge">{{ $mainLabel }}: {{ $comparison['main_count'] }}</span>
            <span class="badge">{{ $backupLabel }}: {{ $comparison['backup_count'] }}</span>
            <span class="badge {{ $hasDifferences ? 'error' : '' }}">
                Diferente: {{ count($missingFromBackup) + count($missingFromMain) }}
            </span>
        </div>
    </div>

    @if (!$hasDifferences)
        <p class="empty">Nu sunt diferente pentru ziua selectata.</p>
    @endif

    @if (count($missingFromBackup) > 0)
        <h3>Exista in {{ $mainLabel }}, lipsesc din {{ $backupLabel }}</h3>
        @include('partials.pos_backup_table', ['rows' => $missingFromBackup])
    @endif

    @if (count($missingFromMain) > 0)
        <h3>Exista in {{ $backupLabel }}, lipsesc din {{ $mainLabel }}</h3>
        @include('partials.pos_backup_table', ['rows' => $missingFromMain])
    @endif
</section>
