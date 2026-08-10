@extends('layouts.admin')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-8 sm:p-10 shadow-[var(--shadow-sm)]">
            <div class="text-center mb-8">
                <a href="/" class="inline-block text-2xl font-bold tracking-tight text-[var(--color-ink)]">
                    Pempek<span class="text-[var(--color-accent)]">.</span> Admin
                </a>
                <h1 class="mt-6 text-xl font-semibold text-[var(--color-ink)]">Masuk ke Dashboard</h1>
                <p class="mt-2 text-sm text-[var(--color-ink-2)]">Masukkan kredensial admin untuk mengakses panel.</p>
            </div>

            @if(session('errors'))
                <div class="mb-6 rounded-[var(--radius-lg)] border border-[var(--color-danger)] bg-[var(--color-danger)]/5 p-4 text-sm text-[var(--color-danger)]">
                    @foreach(session('errors')->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-[var(--color-ink)] mb-1.5">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        class="w-full rounded-[var(--radius-lg)] border border-[var(--color-paper-3)] bg-white px-4 py-2.5 text-sm text-[var(--color-ink)] placeholder:text-[var(--color-ink-3)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]/20 transition-all duration-[var(--dur-normal)]"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-[var(--color-ink)] mb-1.5">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-[var(--radius-lg)] border border-[var(--color-paper-3)] bg-white px-4 py-2.5 text-sm text-[var(--color-ink)] placeholder:text-[var(--color-ink-3)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]/20 transition-all duration-[var(--dur-normal)]"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full rounded-[var(--radius-xl)] bg-[var(--color-accent)] py-3 text-sm font-semibold text-white transition-all duration-[var(--dur-normal)] hover:bg-[var(--color-accent-hover)] active:scale-[0.98]"
                >
                    Masuk
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-[var(--color-ink-3)]">
                Admin default: <code class="bg-[var(--color-paper-2)] px-1.5 py-0.5 rounded-[var(--radius-sm)]">admin@pempek.com</code>
            </p>
        </div>
    </div>
</div>
@endsection