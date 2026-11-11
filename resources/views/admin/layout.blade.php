@extends('tata_letak.aplikasi')

@section('content')
    @php
        $navigasiCms = [
            ['resource' => 'profile', 'label' => 'Profile'],
            ['resource' => 'projects', 'label' => 'Projects'],
            ['resource' => 'skill-categories', 'label' => 'Skill categories'],
            ['resource' => 'skills', 'label' => 'Skills'],
            ['resource' => 'experience', 'label' => 'Experience'],
            ['resource' => 'articles', 'label' => 'Articles'],
            ['resource' => 'messages', 'label' => 'Messages'],
            ['resource' => 'settings', 'label' => 'Settings'],
        ];
    @endphp

    <div class="os-admin">
        <aside class="os-admin__sidebar">
            <div class="os-sidebar">
                <a class="os-navbar__brand" href="{{ route('admin.dashboard') }}">OBSIDIAN SIGNAL <span class="os-badge os-badge--brand">CMS</span></a>
                <nav class="os-sidebar__group" aria-label="CMS navigation">
                    <a class="os-sidebar__item" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>Dashboard</a>
                    @foreach ($navigasiCms as $item)
                        <a
                            class="os-sidebar__item"
                            href="{{ route('admin.cms.index', $item['resource']) }}"
                            @if(request()->route('resource') === $item['resource']) aria-current="page" @endif
                        >{{ $item['label'] }}</a>
                    @endforeach
                </nav>
                <a class="os-sidebar__item" href="{{ route('home') }}">View website</a>
            </div>
        </aside>

        <div class="os-admin__main">
            <header class="os-admin__topbar">
                <span class="os-label os-label--brand">{{ auth()->user()->name }} · Admin</span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="os-btn os-btn--ghost os-btn--sm" type="submit">Logout</button>
                </form>
            </header>

            <main id="main" class="os-admin__content">
                @if (session('status'))
                    <div class="os-alert os-alert--success" role="status">
                        <div class="os-alert__body">{{ session('status') }}</div>
                    </div>
                @endif
                @yield('admin-content')
            </main>
        </div>
    </div>
@endsection
