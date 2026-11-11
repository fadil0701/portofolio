@extends('tata_letak.aplikasi')

@php
    $namaLengkap = $profil?->nama_lengkap ?? 'Fadil';
    $jabatan = $profil?->jabatan ?? 'System Analyst • Data Analyst • Software Developer';
    $ringkasan = $profil?->ringkasan ?? 'Turning complex problems into smart digital solutions.';
    $biografi = $profil?->biografi ?? 'I design systems, turn data into decisions, and build technology that creates measurable impact for businesses.';
    $emailKontak = $profil?->email;
    $teknologiUtama = collect($kategoriKeahlian)
        ->flatMap(fn ($kategori) => $kategori->keahlian->pluck('nama'))
        ->unique()
        ->take(4);
@endphp

@section('title', $namaLengkap . ' | Portofolio')
@section('description', $ringkasan)

@section('content')
    <main id="main" class="os-container os-section os-stack os-home">
        <header class="os-grid os-grid--2 os-home-hero">
            <div class="os-stack os-stack--loose os-home-intro">
                <div class="os-eyebrow">
                    <span class="os-label os-label--brand">// portfolio · Indonesia</span>
                </div>

                <div class="os-stack os-stack--tight">
                    <h1 class="os-display-xl">{{ $namaLengkap }}</h1>
                    <p class="os-label os-label--brand">{{ $jabatan }}</p>
                    <p class="os-text-lg os-text-secondary os-measure">
                        {{ $ringkasan }}
                    </p>
                </div>

                <div class="os-row os-row--tight">
                    <a class="os-btn os-btn--primary" href="#work">Lihat project</a>
                    <a class="os-btn os-btn--outline" href="#contact">Kontak saya</a>
                </div>

                <nav class="os-row os-row--tight os-home-social" aria-label="Social links">
                    @if ($profil?->url_linkedin)
                        <a class="os-btn os-btn--ghost os-btn--sm" href="{{ $profil->url_linkedin }}" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                    @endif
                    @if ($profil?->url_github)
                        <a class="os-btn os-btn--ghost os-btn--sm" href="{{ $profil->url_github }}" target="_blank" rel="noopener noreferrer">GitHub</a>
                    @endif
                    @if ($profil?->url_instagram)
                        <a class="os-btn os-btn--ghost os-btn--sm" href="{{ $profil->url_instagram }}" target="_blank" rel="noopener noreferrer">Instagram</a>
                    @endif
                </nav>
            </div>

            <aside class="os-card os-card--elevated os-home-profile">
                @if ($profil?->url_foto)
                    <img
                        class="os-home-profile__image"
                        src="{{ $profil->url_foto }}"
                        alt="Foto profil {{ $namaLengkap }}"
                        fetchpriority="high"
                    />
                @else
                    <div class="os-home-profile__visual" aria-hidden="true">
                        <span class="os-label">Profile / 01</span>
                        <span class="os-home-profile__monogram">{{ mb_substr($namaLengkap, 0, 1) }}</span>
                        <span class="os-label">System · Data · Software</span>
                    </div>
                @endif
                <div class="os-home-profile__content">
                    <div class="os-row os-row--between os-row--baseline">
                        <span class="os-label">Status</span>
                        <span class="os-badge os-badge--brand">{{ $profil?->status_ketersediaan ?? 'Available' }}</span>
                    </div>
                    <p class="os-home-profile__bio">{{ $biografi }}</p>
                    <div class="os-row os-row--tight">
                        @foreach ($teknologiUtama as $teknologi)
                            <span class="os-badge os-badge--tech">{{ $teknologi }}</span>
                        @endforeach
                    </div>
                    @if ($profil?->lokasi)
                        <span class="os-label os-home-profile__location">{{ $profil->lokasi }}</span>
                    @endif
                </div>
            </aside>
        </header>

        <section class="os-grid os-grid--3 os-home-metrics" aria-label="Portfolio metrics">
            <article class="os-card">
                <div class="os-card__body">
                    <span class="os-label">01 · Published projects</span>
                    <p class="os-display-md">{{ str_pad((string) $jumlahProyek, 2, '0', STR_PAD_LEFT) }}</p>
                    <p class="os-text-sm os-text-secondary">Project yang sudah dipublish dan siap dilihat.</p>
                </div>
            </article>

            <article class="os-card">
                <div class="os-card__body">
                    <span class="os-label">02 · Core skills</span>
                    <p class="os-display-md">{{ str_pad((string) $jumlahKeahlian, 2, '0', STR_PAD_LEFT) }}</p>
                    <p class="os-text-sm os-text-secondary">Keahlian di system analysis, data, dan software engineering.</p>
                </div>
            </article>

            <article class="os-card">
                <div class="os-card__body">
                    <span class="os-label">03 · Work experience</span>
                    <p class="os-display-md">{{ str_pad((string) $pengalaman->count(), 2, '0', STR_PAD_LEFT) }}</p>
                    <p class="os-text-sm os-text-secondary">Role dan pengalaman kerja yang tercatat di portfolio.</p>
                </div>
            </article>
        </section>

        <section id="work" class="os-stack os-stack--loose">
            <div class="os-eyebrow">
                <span class="os-label">Selected projects</span>
            </div>

            <div class="os-grid os-grid--2 os-home-projects">
                @forelse ($proyek as $itemProyek)
                    <article class="os-card os-card--interactive">
                        @if ($itemProyek->url_gambar_pratinjau)
                            <img
                                class="os-home-project__image"
                                src="{{ $itemProyek->url_gambar_pratinjau }}"
                                alt="Preview project {{ $itemProyek->judul }}"
                                loading="lazy"
                            />
                        @else
                            <div class="os-home-project__visual" aria-label="Preview visual {{ $itemProyek->judul }}">
                                <span class="os-label">Case study / {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="os-home-project__visual-title">{{ $itemProyek->kategori }}</span>
                                <span class="os-label">{{ implode(' · ', $itemProyek->tumpukan_teknologi ?? []) }}</span>
                            </div>
                        @endif
                        <div class="os-card__body">
                            <span class="os-badge os-badge--brand">{{ $itemProyek->kategori }}</span>
                            <h2 class="os-heading-md">{{ $itemProyek->judul }}</h2>
                            <p class="os-text-sm os-text-secondary">{{ $itemProyek->ringkasan }}</p>
                            <div class="os-row os-row--tight">
                                @foreach (($itemProyek->tumpukan_teknologi ?? []) as $teknologi)
                                    <span class="os-badge os-badge--tech">{{ $teknologi }}</span>
                                @endforeach
                            </div>
                            @if ($itemProyek->url_demo && $itemProyek->url_demo !== '#')
                                <a class="os-btn os-btn--link" href="{{ $itemProyek->url_demo }}" target="_blank" rel="noopener noreferrer">Open live project</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <article class="os-card os-card--interactive">
                        <div class="os-card__body">
                            <span class="os-label os-label--brand">Portfolio in progress</span>
                            <h2 class="os-heading-md">Project sedang disiapkan</h2>
                            <p class="os-text-sm os-text-secondary">Project yang sudah dipublish akan muncul di bagian ini.</p>
                        </div>
                    </article>
                @endforelse
            </div>
        </section>

        <section class="os-grid os-grid--2" aria-label="Core capabilities">
            <article class="os-card os-card--inset">
                <div class="os-card__body">
                    <div class="os-eyebrow">
                        <span class="os-label">Workflow</span>
                    </div>

                    <div class="os-stack os-stack--tight">
                        <div>
                            <p class="os-text-md os-text-primary">01 · Analyze the problem</p>
                            <p class="os-text-sm os-text-secondary">Pahami kebutuhan user, proses bisnis, dan constraint sebelum menentukan solusi.</p>
                        </div>
                        <div>
                            <p class="os-text-md os-text-primary">02 · Design the system</p>
                            <p class="os-text-sm os-text-secondary">Rancang flow, data model, dan UI yang konsisten serta gampang dikembangkan.</p>
                        </div>
                        <div>
                            <p class="os-text-md os-text-primary">03 · Build & validate</p>
                            <p class="os-text-sm os-text-secondary">Implement solusi, test behavior, dan pastikan hasilnya maintainable.</p>
                        </div>
                    </div>
                </div>
            </article>

            <article class="os-card os-card--elevated">
                <div class="os-card__body">
                    <div class="os-eyebrow">
                        <span class="os-label">Technical skills</span>
                    </div>

                    @forelse ($kategoriKeahlian as $kategori)
                        <div class="os-stack os-stack--tight">
                            <p class="os-label os-label--brand">{{ $kategori->nama }}</p>
                            <div class="os-row os-row--tight">
                                @foreach ($kategori->keahlian as $itemKeahlian)
                                    <span class="os-badge os-badge--tech">{{ $itemKeahlian->nama }}</span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="os-text-sm os-text-secondary">Informasi keahlian akan ditampilkan setelah ditambahkan.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="os-grid os-grid--2" aria-label="Pengalaman dan artikel">
            <article class="os-card os-card--elevated">
                <div class="os-card__body">
                    <div class="os-eyebrow">
                        <span class="os-label">Experience</span>
                    </div>
                    <div class="os-stack os-stack--tight">
                        @forelse ($pengalaman as $itemPengalaman)
                            <div>
                                <p class="os-text-md os-text-primary">{{ $itemPengalaman->jabatan }}</p>
                                <p class="os-text-sm os-text-secondary">
                                    {{ $itemPengalaman->organisasi }} · {{ $itemPengalaman->jenis_pekerjaan }}
                                </p>
                            </div>
                        @empty
                            <p class="os-text-sm os-text-secondary">Informasi pengalaman akan ditampilkan setelah ditambahkan.</p>
                        @endforelse
                    </div>
                </div>
            </article>

            <article class="os-card os-card--inset">
                <div class="os-card__body">
                    <div class="os-eyebrow">
                        <span class="os-label">Latest articles</span>
                    </div>
                    <div class="os-stack os-stack--tight">
                        @forelse ($artikel as $itemArtikel)
                            <div>
                                <p class="os-label os-label--brand">{{ $itemArtikel->kategori }}</p>
                                <p class="os-text-md os-text-primary">{{ $itemArtikel->judul }}</p>
                            </div>
                        @empty
                            <p class="os-text-sm os-text-secondary">Artikel akan tampil setelah diterbitkan.</p>
                        @endforelse
                    </div>
                </div>
            </article>
        </section>

        <section id="contact" class="os-card os-card--elevated">
            <div class="os-card__body">
                <div class="os-eyebrow">
                    <span class="os-label os-label--brand">Contact</span>
                </div>

                <div class="os-grid os-grid--2">
                    <div class="os-stack os-stack--tight">
                        <h2 class="os-heading-lg">Punya project yang perlu dianalisis atau dibangun?</h2>
                        <p class="os-text-md os-text-secondary">
                            Saya terbuka untuk ngobrol soal system analysis, data, dan software development.
                        </p>
                    </div>

                    <div class="os-row os-row--end os-row--nowrap">
                        @if ($emailKontak)
                            <a class="os-btn os-btn--primary" href="mailto:{{ $emailKontak }}">{{ $emailKontak }}</a>
                        @else
                            <span class="os-label">Email contact belum diset di CMS</span>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
