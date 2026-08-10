#!/usr/bin/env python3
"""Generate admin CRUD views (index/create/edit) untuk 7 model."""
import os

BASE = "/home/kiagus/Documents/pempek2/resources/views/admin"

LABEL = {
    "name":"Nama","slug":"Slug","icon":"Icon","ord":"Urutan","status":"Status",
    "description":"Deskripsi","items":"Isi Paket","price":"Harga (Rp)","original_price":"Harga Coret (Rp)",
    "badge":"Badge","is_featured":"Unggulan","title":"Judul","category":"Kategori Artikel",
    "thumbnail":"URL Gambar","author":"Penulis","date":"Tanggal","content":"Konten (HTML)",
    "seo_title":"SEO Title","seo_description":"SEO Description","meta_keywords":"Meta Keywords",
    "avatar":"URL Avatar","kombinasi":"Kombinasi","rating":"Rating (1-5)","comment":"Komentar",
    "question":"Pertanyaan","answer":"Jawaban","subtitle":"Subjudul","button_text":"Teks Tombol",
    "button_link":"Link Tombol","background_image":"URL Gambar Latar","image":"URL Gambar",
    "caption":"Keterangan",
}

# (folder, title, route, fields[(name, type, required)], list_cols)
SPECS = [
    ("kategori", "Kategori", "kategori", [
        ("name", "text", True), ("slug", "text", True), ("icon", "text", False),
        ("ord", "number", False), ("status", "select_active", False),
    ], ["name", "icon", "ord", "status"]),
    ("paket", "Paket", "paket", [
        ("name", "text", True), ("description", "textarea", False),
        ("items", "textarea", False), ("price", "number", True),
        ("original_price", "number", False), ("badge", "text", False),
        ("is_featured", "checkbox", False), ("ord", "number", False),
        ("status", "select_active", False),
    ], ["name", "price", "badge", "status"]),
    ("artikel", "Artikel", "artikel", [
        ("title", "text", True), ("slug", "text", True), ("category", "text", False),
        ("thumbnail", "text", False), ("author", "text", False), ("date", "date", False),
        ("content", "textarea", False), ("seo_title", "text", False),
        ("seo_description", "text", False), ("meta_keywords", "text", False),
        ("status", "select_draft", False),
    ], ["title", "category", "date", "status"]),
    ("testimoni", "Testimoni", "testimoni", [
        ("name", "text", True), ("avatar", "text", False), ("kombinasi", "text", False),
        ("rating", "number", False), ("comment", "textarea", True),
        ("date", "date", False), ("status", "select_active", False), ("ord", "number", False),
    ], ["name", "kombinasi", "rating", "status"]),
    ("faq", "FAQ", "faq", [
        ("question", "text", True), ("answer", "textarea", True),
        ("status", "select_active", False), ("ord", "number", False),
    ], ["question", "status"]),
    ("banner", "Banner", "banner", [
        ("title", "text", True), ("subtitle", "text", False),
        ("button_text", "text", False), ("button_link", "text", False),
        ("background_image", "text", True), ("status", "select_active", False),
        ("ord", "number", False),
    ], ["title", "subtitle", "status"]),
    ("galeri", "Galeri", "galeri", [
        ("image", "text", True), ("caption", "text", False), ("ord", "number", False),
    ], ["image", "caption"]),
]

def field_html(f, val_expr):
    name, ftype, req = f
    lbl = LABEL.get(name, name.replace("_", " ").title())
    req_attr = " required" if req else ""
    if ftype == "checkbox":
        return f'<label class="flex items-center gap-2 text-sm text-[var(--color-ink-2)]">\n            <input type="checkbox" name="{name}" value="1" @checked(old(\'{name}\', {val_expr}))> {lbl}\n        </label>'
    if ftype == "textarea":
        return f'<label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">{lbl}</label>\n            <textarea name="{name}" rows="4" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{{{ old(\'{name}\', {val_expr}) }}}}</textarea>'
    if ftype in ("select_active", "select_draft"):
        opts = [("active", "Aktif"), ("inactive", "Nonaktif")] if ftype == "select_active" else [("draft", "Draft"), ("published", "Terbit")]
        o = "".join(f'<option value="{v}" @selected(old(\'{name}\', {val_expr}) === \'{v}\')>{l}</option>' for v, l in opts)
        return f'<label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">{lbl}</label>\n            <select name="{name}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{o}</select>'
    if ftype == "date":
        return f'<label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">{lbl}</label>\n            <input type="date" name="{name}" value="{{{{ old(\'{name}\', {val_expr}) }}}}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">'
    if ftype == "number":
        return f'<label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">{lbl}</label>\n            <input type="number" name="{name}" min="0" value="{{{{ old(\'{name}\', {val_expr}) }}}}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">'
    return f'<label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">{lbl}</label>\n            <input type="text" name="{name}"{req_attr} value="{{{{ old(\'{name}\', {val_expr}) }}}}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">'

def form_body(fields, item_expr):
    out, grid = [], []
    for f in fields:
        if f[1] in ("textarea", "checkbox"):
            if grid:
                out.append('<div class="grid gap-6 sm:grid-cols-2">' + "\n".join(grid) + "\n    </div>")
                grid = []
            out.append("<div>\n    " + field_html(f, item_expr(f[0])) + "\n</div>")
        else:
            grid.append("<div>\n    " + field_html(f, item_expr(f[0])) + "\n</div>")
    if grid:
        out.append('<div class="grid gap-6 sm:grid-cols-2">' + "\n".join(grid) + "\n    </div>")
    return "\n    ".join(out)

def td_cell(c, mv):
    if c == "status":
        return (f'<td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium '
                f'{{{{ $%s->status === \'active\' ? \'bg-emerald-50 text-emerald-700\' : \'bg-gray-100 text-gray-600\' }}}}">{{{{ $%s->status }}}}</span></td>' % (mv, mv))
    if c == "ord":
        return f'<td class="px-6 py-4 tabular-nums">{{{{ $%s->ord }}}}</td>' % mv
    if c == "price":
        return f'<td class="px-6 py-4 font-semibold tabular-nums text-[var(--color-ink)]">Rp{{{{ number_format($%s->price) }}}}</td>' % mv
    if c == "rating":
        return f'<td class="px-6 py-4 tabular-nums">{{{{ $%s->rating }}}}★</td>' % mv
    if c in ("image", "thumbnail", "background_image", "avatar"):
        return f'<td class="px-6 py-4"><img src="{{{{ asset($%s->%s) }}}}" class="h-11 w-11 rounded-[var(--radius-md)] object-cover" alt=""></td>' % (mv, c)
    if c == "question":
        return f'<td class="px-6 py-4 font-medium text-[var(--color-ink)]">{{{{ Str::limit($%s->question, 50) }}}}</td>' % mv
    return f'<td class="px-6 py-4">{{{{ $%s->%s }}}}</td>' % (mv, c)

for folder, title, rt, fields, list_cols in SPECS:
    fp = os.path.join(BASE, folder)
    os.makedirs(fp, exist_ok=True)
    mv = "cat" if folder == "kategori" else "item"

    # INDEX
    ths = "".join(f'<th class="px-6 py-4">{LABEL.get(c, c.title())}</th>' for c in list_cols)
    tds = "".join(td_cell(c, mv) for c in list_cols)
    actions = (f'<td class="px-6 py-4 text-right"><div class="flex justify-end gap-2">'
               f'<a href="{{{{ route(\'admin.{rt}.edit\', ${mv}) }}}}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a>'
               f'<form method="POST" action="{{{{ route(\'admin.{rt}.destroy\', ${mv}) }}}}" onsubmit="return confirm(\'Hapus {title} ini?\')">'
               f'@csrf @method(\'DELETE\')'
               f'<button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button>'
               f'</form></div></td>')
    index = f"""@extends('admin.layouts.app')

@section('title', '{title} — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => '{title}',
    'description' => 'Kelola data {title.lower()}.',
    'actionRoute' => route('admin.{rt}.create'),
    'actionLabel' => 'Tambah {title}',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                {ths}<th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($items as ${mv})
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    {tds}
                    {actions}
                </tr>
            @empty
                <tr><td colspan="{len(list_cols)+1}" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada {title.lower()}.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
"""
    with open(os.path.join(fp, "index.blade.php"), "w") as f:
        f.write(index)

    # CREATE / EDIT
    for action, label_btn, method in [("create", "Simpan", ""), ("edit", "Simpan Perubahan", "@method('PUT')")]:
        if action == "create":
            expr = lambda f: "''"
        else:
            expr = lambda f, mv=mv: f"${mv}->{f}"
        fhtml = form_body(fields, expr)
        model_arg = f", ${mv}" if action == "edit" else ""
        page = f"""@extends('admin.layouts.app')

@section('title', '{"Edit" if action=="edit" else "Tambah"} {title} — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => '{"Edit" if action=="edit" else "Tambah"} {title}',
    'description' => '{"Perbarui" if action=="edit" else "Lengkapi"} data {title.lower()}.',
    'actionRoute' => route('admin.{rt}.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{{{ route('admin.{rt}.{"update" if action=="edit" else "store"}'{model_arg}) }}}}" class="space-y-6">
        @csrf
        {method}
        {fhtml}

        <div class="flex justify-end border-t border-[var(--color-paper-3)] pt-6">
            <button type="submit" class="rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-[var(--color-accent-hover)]">
                {label_btn}
            </button>
        </div>
    </form>
</div>
@endsection
"""
        with open(os.path.join(fp, f"{action}.blade.php"), "w") as f:
            f.write(page)

print("OK, generated:", len(SPECS) * 3, "views")
