<?php

namespace Tests\Unit;

use App\Support\Csv;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    public static function dangerousValues(): array
    {
        return [
            ['=SUM(1,1)'],
            ['+cmd'],
            ['-2+3'],
            ['@SUM(A1:A2)'],
            ["\t=SUM(1,1)"],
        ];
    }

    #[DataProvider('dangerousValues')]
    public function test_it_neutralizes_spreadsheet_formulas(string $value): void
    {
        $this->assertSame("'".$value, Csv::safeCell($value));
    }

    public function test_it_preserves_regular_values(): void
    {
        $this->assertSame('Kejaksaan Tinggi Jawa Barat', Csv::safeCell('Kejaksaan Tinggi Jawa Barat'));
        $this->assertSame('', Csv::safeCell(null));
    }
}
