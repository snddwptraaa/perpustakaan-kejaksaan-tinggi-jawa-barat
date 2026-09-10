<?php

namespace App\Services;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class XlsxReportWriter
{
    /**
     * @param  callable(callable(array<int, mixed>): void): void  $writeRows
     * @param  array<string, string>  $meta
     */
    public function download(string $filename, array $headings, callable $writeRows, ?string $title = null, array $meta = []): BinaryFileResponse
    {
        $path = tempnam(storage_path('app'), 'laporan-');
        if ($path === false) {
            throw new RuntimeException('File sementara laporan tidak dapat dibuat.');
        }

        $writer = new Writer;
        try {
            $writer->openToFile($path);
            if ($title !== null) {
                $writer->addRow(Row::fromValues([$title]));
            }
            foreach ($meta as $key => $value) {
                $writer->addRow(Row::fromValues([(string) $key, (string) $value]));
            }
            if ($title !== null || $meta !== []) {
                $writer->addRow(Row::fromValues([]));
            }
            $writer->addRow(Row::fromValues($headings));
            $writeRows(fn (array $values) => $writer->addRow(Row::fromValues(array_values($values))));
            $writer->close();
        } catch (\Throwable $exception) {
            @unlink($path);
            throw $exception;
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
