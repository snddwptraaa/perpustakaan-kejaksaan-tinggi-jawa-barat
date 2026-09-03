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
     */
    public function download(string $filename, array $headings, callable $writeRows): BinaryFileResponse
    {
        $path = tempnam(storage_path('app'), 'laporan-');
        if ($path === false) {
            throw new RuntimeException('File sementara laporan tidak dapat dibuat.');
        }

        $writer = new Writer;
        try {
            $writer->openToFile($path);
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
