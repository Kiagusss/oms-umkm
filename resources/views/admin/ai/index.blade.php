@extends('admin.layouts.app')

@section('title', 'AI Assistant — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'AI Assistant',
    'description' => 'Chat dengan Dia Pempek (mode admin) — agent punya akses tools baca-data (produk, ringkasan pesanan).',
])

{{-- Flash messages --}}
@if (session('error'))
    <div class="mb-4 rounded-[var(--radius-lg)] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        {{ session('error') }}
    </div>
@endif
@if (session('success'))
    <div class="mb-4 rounded-[var(--radius-lg)] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    {{-- ─── Chat panel (kolom utama) ──────────────────────────────── --}}
    <div class="lg:col-span-2 rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8 shadow-sm">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-lg font-bold text-[var(--color-ink)]">Percakapan</h2>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                <span class="font-semibold text-[var(--color-ink-2)]">Provider:</span>
                <code class="rounded bg-[var(--color-paper-2)] px-2 py-0.5 text-xs">{{ config('ai.default') }}</code>
            </div>
        </div>

        <div id="chat-log" class="mb-4 max-h-[480px] min-h-[320px] space-y-3 overflow-y-auto rounded-[var(--radius-md)] bg-[var(--color-paper)] p-4 text-sm">
            <div class="flex justify-start">
                <div class="max-w-[80%] rounded-2xl bg-white px-4 py-2.5 text-[var(--color-ink-2)] shadow-sm">
                    Halo admin! Aku Dia Pempek dalam mode admin. Tanya apa saja tentang produk, stok, atau ringkasan pesanan.
                </div>
            </div>
        </div>

        <form id="chat-form" class="flex gap-2">
            <input type="hidden" name="conversation_id" id="conversation_id">
            <input
                type="text"
                name="message"
                id="message-input"
                required
                maxlength="4000"
                placeholder="Tanya tentang produk / pesanan…"
                autocomplete="off"
                class="flex-1 rounded-[var(--radius-md)] border border-[var(--color-paper-3)] bg-white px-4 py-2.5 text-sm focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
            >
            <button
                type="submit"
                id="send-btn"
                class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[var(--color-accent-hover)] disabled:cursor-not-allowed disabled:opacity-50"
            >
                Kirim
            </button>
        </form>
    </div>

    {{-- ─── Info panel (kolom samping) ────────────────────────────── --}}
    <aside class="space-y-6">
        <div class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-[var(--color-ink)]">Tentang AI</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex flex-col gap-1">
                    <dt class="font-semibold text-[var(--color-ink)]">Agent</dt>
                    <dd><code class="rounded bg-[var(--color-paper-2)] px-2 py-0.5 text-xs">DiaPempekAdminAgent</code></dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="font-semibold text-[var(--color-ink)]">Tools</dt>
                    <dd class="text-[var(--color-ink-2)]">ListProductsTool, GetOrderSummaryTool</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="font-semibold text-[var(--color-ink)]">Provider</dt>
                    <dd class="text-[var(--color-ink-2)]">OpenAI-compatible (generic)</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="font-semibold text-[var(--color-ink)]">Endpoint</dt>
                    <dd class="break-all font-mono text-xs text-[var(--color-ink-2)]">{{ config('services.chat.base_url') }}</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="font-semibold text-[var(--color-ink)]">Model</dt>
                    <dd><code class="rounded bg-[var(--color-paper-2)] px-2 py-0.5 text-xs">{{ config('services.chat.model') }}</code></dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="font-semibold text-[var(--color-ink)]">Memory</dt>
                    <dd class="text-[var(--color-ink-2)]">Auto-saved ke tabel <code class="rounded bg-[var(--color-paper-2)] px-1 text-xs">agent_conversations</code></dd>
                </div>
            </dl>
        </div>

        <div class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-[var(--color-ink)]">Sesi</h2>
            <p class="mt-2 text-xs text-[var(--color-ink-3)]">
                Conversation ID disimpan otomatis. Klik tombol di bawah untuk mulai percakapan baru.
            </p>
            <button
                type="button"
                id="reset-btn"
                class="mt-4 w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] bg-white px-4 py-2 text-sm font-semibold text-[var(--color-ink-2)] transition hover:bg-[var(--color-paper-2)]"
            >
                Mulai percakapan baru
            </button>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const log = document.getElementById('chat-log');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    const convIdInput = document.getElementById('conversation_id');
    const resetBtn = document.getElementById('reset-btn');

    const GREETING = 'Halo admin! Aku Dia Pempek dalam mode admin. Tanya apa saja tentang produk, stok, atau ringkasan pesanan.';

    function append(role, text) {
        const wrap = document.createElement('div');
        wrap.className = role === 'user' ? 'flex justify-end' : 'flex justify-start';
        const bubble = document.createElement('div');
        bubble.className = role === 'user'
            ? 'max-w-[80%] rounded-2xl bg-[var(--color-accent)] px-4 py-2.5 text-white shadow-sm whitespace-pre-wrap'
            : 'max-w-[80%] rounded-2xl bg-white px-4 py-2.5 text-[var(--color-ink-2)] shadow-sm whitespace-pre-wrap';
        bubble.textContent = text;
        wrap.appendChild(bubble);
        log.appendChild(wrap);
        log.scrollTop = log.scrollHeight;
    }

    function setLoading(on) {
        sendBtn.disabled = on;
        input.disabled = on;
        sendBtn.textContent = on ? 'Mengirim…' : 'Kirim';
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        append('user', message);
        input.value = '';
        setLoading(true);

        try {
            const res = await fetch('{{ route('admin.ai.chat') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    message,
                    conversation_id: convIdInput.value || null,
                }),
            });
            const json = await res.json();
            if (!res.ok) {
                append('assistant', '⚠️ ' + (json.error || 'Gagal mendapat balasan.'));
            } else {
                append('assistant', json.reply);
                if (json.conversation_id) convIdInput.value = json.conversation_id;
            }
        } catch (err) {
            append('assistant', '⚠️ Terjadi kesalahan jaringan.');
        } finally {
            setLoading(false);
            input.focus();
        }
    });

    resetBtn.addEventListener('click', () => {
        convIdInput.value = '';
        log.innerHTML = '';
        append('assistant', GREETING);
        input.focus();
    });
})();
</script>
@endpush