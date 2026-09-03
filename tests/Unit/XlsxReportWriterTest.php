<?php

namespace Tests\Unit;

use App\Services\XlsxReportWriter;
use Tests\TestCase;
use ZipArchive;

class XlsxReportWriterTest extends TestCase
{
    public function test_it_creates_a_readable_xlsx_archive_with_headers_and_rows(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('Smoke test requires the PHP ZIP extension.');
        }

        $response = app(XlsxReportWriter::class)->download('uji.xlsx', ['Nama'], function (callable $append): void {
            $append(['Contoh']);
        });

        $path = $response->getFile()->getPathname();
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        $this->assertIsString($sheet);
        $this->assertStringContainsString('Nama', $sheet);
        $this->assertStringContainsString('Contoh', $sheet);
        @unlink($path);
    }
}
