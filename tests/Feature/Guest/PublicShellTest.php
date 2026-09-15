<?php

namespace Tests\Feature\Guest;

use Tests\TestCase;

class PublicShellTest extends TestCase
{
    public function test_home_page_uses_the_shared_public_navigation(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('public-header', false)
            ->assertSee('rel="icon"', false)
            ->assertSee('images/logo.svg', false)
            ->assertSee('apple-touch-icon.png', false)
            ->assertSee(route('home').'#hero', false)
            ->assertSee(route('home').'#layanan', false)
            ->assertSee(route('home').'#tentang', false)
            ->assertSee(route('home').'#kontak', false)
            ->assertSee(route('login'), false)
            ->assertDontSee('#informasi', false);

        $content = $response->getContent();
        $this->assertSame(1, substr_count($content, 'class="public-header"'));
        $this->assertMatchesRegularExpression('/<header[\s\S]*class="public-header"[\s\S]*<\/header>/', $content);

        preg_match('/<header[\s\S]*class="public-header"[\s\S]*?<\/header>/', $content, $header);

        $this->assertStringNotContainsString('Katalog buku', $header[0]);
        $this->assertStringNotContainsString(route('login'), $header[0]);
        $this->assertStringContainsString("activeSection === 'layanan'", $header[0]);
        $this->assertStringContainsString('aria-current', $header[0]);
    }

    public function test_home_page_explains_that_catalog_access_starts_with_the_guest_book(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSeeText('Layanan')
            ->assertSeeText('Layanan saat Anda berkunjung')
            ->assertSeeText('Baca di tempat')
            ->assertSeeText('Peminjaman')
            ->assertSeeText('Referensi')
            ->assertSeeText('Repositori digital')
            ->assertSeeText('Isi buku tamu terlebih dahulu untuk melanjutkan ke katalog.')
            ->assertSeeText('Referensi yang membantu langkah Anda lebih pasti.')
            ->assertSeeText('Selengkapnya');
    }
}
