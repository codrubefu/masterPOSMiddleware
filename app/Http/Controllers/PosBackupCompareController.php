<?php

namespace App\Http\Controllers;

use App\Models\TrzCfePOS;
use App\Models\TrzCfePOSSent;
use App\Models\TrzDetCfPOS;
use App\Models\TrzDetCfPOSSent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PosBackupCompareController extends Controller
{
    private const DATE_TOLERANCE_MINUTES = 10;

    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $day = Carbon::parse($date);

        $headerComparison = $this->compareTables(
            TrzCfePOS::class,
            TrzCfePOSSent::class,
            'datac',
            ['nrbonfint'],
            ['data', 'datac']
        );

        $detailComparison = $this->compareTables(
            TrzDetCfPOS::class,
            TrzDetCfPOSSent::class,
            'data',
            ['idtrzf', 'nrbonf'],
            ['data']
        );

        return view('pos_backup_compare', [
            'date' => $day->toDateString(),
            'toleranceMinutes' => self::DATE_TOLERANCE_MINUTES,
            'headerComparison' => $headerComparison($day),
            'detailComparison' => $detailComparison($day),
        ]);
    }

    private function compareTables(
        string $mainModel,
        string $backupModel,
        string $filterDateField,
        array $ignoredFields,
        array $dateFields
    ): callable {
        return function (Carbon $day) use ($mainModel, $backupModel, $filterDateField, $ignoredFields, $dateFields): array {
            $mainRows = $this->rowsForDay($mainModel, $filterDateField, $day);
            $backupRows = $this->rowsForDay($backupModel, $filterDateField, $day);

            $unusedBackup = array_values($backupRows);
            $missingFromBackup = [];

            foreach ($mainRows as $mainRow) {
                $matchIndex = $this->findMatchingRow($mainRow, $unusedBackup, $ignoredFields, $dateFields);

                if ($matchIndex === null) {
                    $missingFromBackup[] = $mainRow;
                    continue;
                }

                array_splice($unusedBackup, $matchIndex, 1);
            }

            return [
                'main_count' => count($mainRows),
                'backup_count' => count($backupRows),
                'missing_from_backup' => $missingFromBackup,
                'missing_from_main' => $unusedBackup,
            ];
        };
    }

    private function rowsForDay(string $modelClass, string $dateField, Carbon $day): array
    {
        return $modelClass::query()
            ->whereBetween($dateField, [
                $day->copy()->startOfDay(),
                $day->copy()->endOfDay(),
            ])
            ->orderBy($dateField)
            ->get()
            ->map(fn (Model $row) => $row->getAttributes())
            ->all();
    }

    private function findMatchingRow(array $mainRow, array $backupRows, array $ignoredFields, array $dateFields): ?int
    {
        foreach ($backupRows as $index => $backupRow) {
            if ($this->rowsMatch($mainRow, $backupRow, $ignoredFields, $dateFields)) {
                return $index;
            }
        }

        return null;
    }

    private function rowsMatch(array $mainRow, array $backupRow, array $ignoredFields, array $dateFields): bool
    {
        $fields = array_unique(array_merge(array_keys($mainRow), array_keys($backupRow)));
        $ignored = array_flip($ignoredFields);
        $dates = array_flip($dateFields);

        foreach ($fields as $field) {
            if (isset($ignored[$field])) {
                continue;
            }

            if (isset($dates[$field])) {
                if (!$this->datesMatch($mainRow[$field] ?? null, $backupRow[$field] ?? null)) {
                    return false;
                }

                continue;
            }

            if ($this->normalizeValue($mainRow[$field] ?? null) !== $this->normalizeValue($backupRow[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }

    private function datesMatch($mainValue, $backupValue): bool
    {
        if ($mainValue === null || $backupValue === null) {
            return $mainValue === $backupValue;
        }

        $mainDate = Carbon::parse($mainValue);
        $backupDate = Carbon::parse($backupValue);

        return abs($mainDate->diffInSeconds($backupDate, false)) <= self::DATE_TOLERANCE_MINUTES * 60;
    }

    private function normalizeValue($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_numeric($value)) {
            return rtrim(rtrim(number_format((float) $value, 6, '.', ''), '0'), '.');
        }

        return trim((string) $value);
    }
}
