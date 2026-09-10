<header
    x-data="{
        menuOpen: false,
        activeSection: @js(request()->routeIs('home') ? 'hero' : null),
        scrollTimer: null,
        updateActiveSection() {
            const sectionIds = ['hero', 'layanan', 'tentang', 'kontak'];
            const sections = sectionIds
                .map((id) => document.getElementById(id))
                .filter(Boolean);

            if (!sections.length) return;

            if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) {
                this.activeSection = sections.at(-1).id;
                return;
            }

            const marker = window.scrollY + this.$el.offsetHeight + 32;
            this.activeSection = sections.reduce(
                (current, section) => section.offsetTop <= marker ? section.id : current,
                sections[0].id,
            );
        },
        queueSectionUpdate() {
            this.updateActiveSection();
            window.clearTimeout(this.scrollTimer);
            this.scrollTimer = window.setTimeout(() => this.updateActiveSection(), 150);
        },
    }"
    x-init="$nextTick(() => updateActiveSection())"
    @scroll.window="queueSectionUpdate()"
    @resize.window.debounce.150ms="updateActiveSection()"
    @keydown.escape.window="menuOpen = false"
    @click.outside="menuOpen = false"
    class="public-header"
>
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 lg:px-8">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" wire:navigate>
            <img src="{{ asset('images/logo.svg') }}" alt="Logo Kejaksaan Tinggi Jawa Barat" width="40" height="44" class="h-11 w-10 shrink-0 object-contain">
            <span class="min-w-0"><span class="block text-[10px] font-bold uppercase tracking-[0.12em] text-kejati sm:text-xs">Kejaksaan Tinggi Jawa Barat</span><span class="block text-base font-bold tracking-tight text-kejati-dark sm:text-lg">Perpustakaan Digital</span></span>
        </a>
        <nav class="hidden items-center gap-1 lg:flex" aria-label="Navigasi utama">
            <a href="{{ route('home') }}#hero" @click="activeSection = 'hero'" :aria-current="activeSection === 'hero' ? 'location' : null" class="public-nav-link">Beranda</a>
            <a href="{{ route('home') }}#layanan" @click="activeSection = 'layanan'" :aria-current="activeSection === 'layanan' ? 'location' : null" class="public-nav-link">Layanan</a>
            <a href="{{ route('home') }}#tentang" @click="activeSection = 'tentang'" :aria-current="activeSection === 'tentang' ? 'location' : null" class="public-nav-link">Tentang</a>
            <a href="{{ route('home') }}#kontak" @click="activeSection = 'kontak'" :aria-current="activeSection === 'kontak' ? 'location' : null" class="public-nav-link">Kontak</a>
        </nav>
        <button type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-controls="mobile-navigation" :aria-label="menuOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi'" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-stone-200 text-kejati lg:hidden">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path x-show="!menuOpen" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/><path x-show="menuOpen" stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>
    <nav id="mobile-navigation" x-cloak x-show="menuOpen" x-transition.opacity.duration.150ms @click="if ($event.target.closest('a')) menuOpen = false" class="border-t border-stone-200 bg-white px-5 py-4 lg:hidden" aria-label="Navigasi mobile">
        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('home') }}#hero" @click="activeSection = 'hero'" :aria-current="activeSection === 'hero' ? 'location' : null" class="public-nav-link">Beranda</a>
            <a href="{{ route('home') }}#layanan" @click="activeSection = 'layanan'" :aria-current="activeSection === 'layanan' ? 'location' : null" class="public-nav-link">Layanan</a>
            <a href="{{ route('home') }}#tentang" @click="activeSection = 'tentang'" :aria-current="activeSection === 'tentang' ? 'location' : null" class="public-nav-link">Tentang</a>
            <a href="{{ route('home') }}#kontak" @click="activeSection = 'kontak'" :aria-current="activeSection === 'kontak' ? 'location' : null" class="public-nav-link">Kontak</a>
        </div>
    </nav>
</header>
