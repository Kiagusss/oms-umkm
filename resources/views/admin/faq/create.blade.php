@extends('admin.layouts.app')

@section('title', 'Tambah FAQ — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Tambah FAQ',
    'description' => 'Lengkapi data faq.',
    'actionRoute' => route('admin.faq.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.faq.store') }}" class="space-y-6">
        @csrf
        
        <div class="grid gap-6 sm:grid-cols-2"><div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Pertanyaan</label>
            <input type="text" name="question" required value="{{ old('question', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
    </div>
    <div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Jawaban</label>
            <textarea name="answer" rows="4" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('answer', '') }}</textarea>
</div>
    <div class="grid gap-6 sm:grid-cols-2"><div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Status</label>
            <select name="status" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm"><option value="active" @selected(old('status', '') === 'active')>Aktif</option><option value="inactive" @selected(old('status', '') === 'inactive')>Nonaktif</option></select>
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Urutan</label>
            <input type="number" name="ord" min="0" value="{{ old('ord', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
    </div>

        <div class="flex justify-end border-t border-[var(--color-paper-3)] pt-6">
            <button type="submit" class="rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-[var(--color-accent-hover)]">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection
