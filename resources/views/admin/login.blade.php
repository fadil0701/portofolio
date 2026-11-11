@extends('tata_letak.aplikasi')

@section('title', 'Login | Admin')
@section('description', 'Login ke dashboard admin OBSIDIAN SIGNAL.')

@section('content')
    <main id="main" class="os-container os-section">
        <section class="os-card os-card--elevated os-card__body">
            <div class="os-stack os-stack--tight">
                <span class="os-label os-label--brand">// admin</span>
                <h1 class="os-heading-lg">Admin Login</h1>
                <p class="os-text-sm os-text-secondary">Login pakai akun admin untuk lanjut.</p>
            </div>

            <form method="POST" action="{{ route('admin.login.submit') }}" class="os-stack">
                @csrf

                <label class="os-field">
                    <span class="os-field__label">Email</span>
                    <input
                        class="os-input"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        required
                        autofocus
                    />
                    @error('email')
                        <span class="os-field__error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="os-field">
                    <span class="os-field__label">Password</span>
                    <input
                        class="os-input"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    />
                    @error('password')
                        <span class="os-field__error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="os-row os-row--tight">
                    <input type="checkbox" name="remember" value="1" />
                    <span class="os-text-sm os-text-secondary">Remember me</span>
                </label>

                <button class="os-btn os-btn--primary os-btn--block" type="submit">Login</button>
            </form>
        </section>
    </main>
@endsection
