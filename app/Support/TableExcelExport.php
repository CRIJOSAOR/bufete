<?php

namespace App\Support;

use BackedEnum;
use DateTimeInterface;
use Filament\Actions\Action;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Stringable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class TableExcelExport
{
    public static function action(): Action
    {
        return Action::make('downloadExcel')
            ->label('Descargar Excel')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(fn (HasTable $livewire) => app(self::class)->download($livewire));
    }

    public function download(HasTable $livewire): BinaryFileResponse
    {
        $table = $livewire->getTable();
        $columns = array_map(
            static fn (Column $column): Column => clone $column,
            array_values($table->getVisibleColumns()),
        );
        $query = $livewire->getTableQueryForExport();

        foreach ($columns as $column) {
            $column->applyRelationshipAggregates($query);
            $column->applyEagerLoading($query);
        }

        $fileName = str(class_basename($livewire))->kebab().'-'.now()->format('Ymd-His').'.xlsx';
        $filePath = tempnam(sys_get_temp_dir(), 'bufete-excel-');

        if ($filePath === false) {
            throw new \RuntimeException('No se pudo preparar el archivo de Excel.');
        }

        $writer = new Writer;

        try {
            $writer->openToFile($filePath);
            $writer->addRow(Row::fromValues(array_map(
                fn (Column $column): string => $this->cellString($column->getLabel() ?? $column->getName()),
                $columns,
            )));

            $query->chunk(500, function ($records) use ($columns, $writer): void {
                foreach ($records as $record) {
                    if (! $record instanceof Model) {
                        continue;
                    }

                    $values = [];

                    foreach ($columns as $column) {
                        $column->record($record);
                        $column->clearCachedState();
                        $state = $column->getState();

                        if ($column instanceof TextColumn) {
                            $relatedRecords = $column->getRelatedRecords();
                            $relatedRecord = count($relatedRecords) === 1 ? reset($relatedRecords) : null;
                            $state = $column->formatState($state, $relatedRecord);
                        }

                        $values[] = $this->normalizeCellValue($state);
                        $column->clearCachedState();
                    }

                    $writer->addRow(Row::fromValues($values));
                }
            });

            $writer->close();
        } catch (Throwable $exception) {
            $writer->close();
            unlink($filePath);

            throw $exception;
        }

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function normalizeCellValue(mixed $value): bool|float|int|string|null
    {
        if ($value instanceof BackedEnum) {
            return $this->normalizeCellValue($value->value);
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof Model) {
            return $value->getKey();
        }

        if ($value instanceof Htmlable) {
            $value = $value->toHtml();
        }

        if ($value instanceof Stringable) {
            $value = (string) $value;
        }

        if (is_array($value) || $value instanceof \Traversable) {
            return implode(', ', array_filter(
                array_map(fn (mixed $item): string => $this->cellString($this->normalizeCellValue($item)), is_array($value) ? $value : iterator_to_array($value)),
                static fn (string $item): bool => $item !== '',
            ));
        }

        if (is_bool($value) || is_float($value) || is_int($value) || $value === null) {
            return $value;
        }

        $value = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (preg_match('/^[\x00-\x20]*[=+\-@]/u', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }

    private function cellString(mixed $value): string
    {
        return $value === null ? '' : (string) $value;
    }
}
