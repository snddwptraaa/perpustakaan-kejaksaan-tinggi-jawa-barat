<?php

namespace Tests\Unit;

use App\Support\Isbn;
use PHPUnit\Framework\TestCase;

class IsbnTest extends TestCase
{
    public function test_accepts_isbn10_with_separators(): void
    {
        // ISBN-10 valid: 0-306-40615-2
        $this->assertSame('0306406152', Isbn::normalize('0-306-40615-2'));
        $this->assertSame('0306406152', Isbn::normalize('0 306 40615 2'));
    }

    public function test_accepts_isbn10_ending_with_x(): void
    {
        // ISBN-10 dengan check digit X
        $this->assertSame('080442957X', Isbn::normalize('0-8044-2957-X'));
        $this->assertSame('080442957X', Isbn::normalize('080442957x'));
    }

    public function test_accepts_isbn13_with_separators(): void
    {
        // ISBN-13 valid: 978-0-13-468599-1
        $this->assertSame('9780134685991', Isbn::normalize('978-0-13-468599-1'));
        $this->assertSame('9780134685991', Isbn::normalize('9780134685991'));
    }

    public function test_empty_input_returns_empty_string(): void
    {
        $this->assertSame('', Isbn::normalize(null));
        $this->assertSame('', Isbn::normalize(''));
        $this->assertSame('', Isbn::normalize('   '));
    }

    public function test_rejects_invalid_checksum_isbn10(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Checksum ISBN-10 tidak valid.');
        Isbn::normalize('0306406153'); // check digit salah
    }

    public function test_rejects_invalid_checksum_isbn13(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Checksum ISBN-13 tidak valid.');
        Isbn::normalize('9780134685992'); // check digit salah
    }

    public function test_rejects_malformed_length_or_characters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Isbn::normalize('12345');
    }

    public function test_rejects_letters_other_than_trailing_x(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Isbn::normalize('ABCDEFGHIJ');
    }
}
