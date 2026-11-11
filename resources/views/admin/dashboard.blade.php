@extends('admin.layout')

@section('title', 'Admin dashboard')

@section('admin-content')
    <header class="os-stack os-stack--tight">
        <span class="os-label os-label--brand">// admin / overview</span>
        <h1 class="os-heading-lg">Dashboard</h1>
        <p class="os-text-md os-text-secondary">Manage portfolio content and incoming messages.</p>
    </header>

    <section class="os-grid os-grid--4" aria-label="CMS summary">
        @foreach ([
            ['Projects', $jumlahProyek, 'projects'],
            ['Articles', $jumlahArtikel, 'articles'],
            ['Skills', $jumlahKeahlian, 'skills'],
            ['New messages', $pesanBaru, 'messages'],
        ] as [$label, $value, $resource])
            <a class="os-metric" href="{{ route('admin.cms.index', $resource) }}">
                <span class="os-metric__label">{{ $label }}</span>
                <span class="os-metric__value">{{ $value }}</span>
                <span class="os-text-sm os-text-secondary">Open manager</span>
            </a>
        @endforeach
    </section>

    <section class="os-card">
        <div class="os-card__header">
            <h2 class="os-heading-md">Recent projects</h2>
            <a class="os-btn os-btn--outline os-btn--sm" href="{{ route('admin.cms.index', 'projects') }}">All projects</a>
        </div>
        @if ($proyekTerbaru->isNotEmpty())
            <div class="os-table-wrap">
                <div class="os-table-scroll">
                    <table class="os-table">
                        <thead>
                            <tr><th>Title</th><th>Category</th><th>Status</th><th>Updated</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($proyekTerbaru as $proyek)
                                <tr>
                                    <td>{{ $proyek->judul }}</td>
                                    <td>{{ $proyek->kategori }}</td>
                                    <td><span class="os-badge os-badge--tech">{{ ucfirst($proyek->status) }}</span></td>
                                    <td>{{ $proyek->updated_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="os-card__body">
                <p class="os-text-secondary">No projects yet. Add a project to start building your portfolio.</p>
                <a class="os-btn os-btn--primary" href="{{ route('admin.cms.create', 'projects') }}">Create project</a>
            </div>
        @endif
    </section>
@endsection
