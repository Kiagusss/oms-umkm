# Pempek Project: AI SDK Integration

## Stack
- Laravel 13.8.13 + PHP 8.4.23, SQLite
- laravel/ai SDK v0.10.3 (release 2026-08-06)
- AI Provider: 'chat' (OpenAI-compatible custom), generic endpoint

## AI Provider (chat)
- Driver: `openai-compatible`
- Base URL: `https://rzgwipd.abc-tunnel.us/v1` (CHAT_API_BASE)
- Model: `gratis` (CHAT_MODEL)
- API Key: `LOCAL_AI_API_KEY=sk-9882109863a59c6c-cbkkit-d8aa624b`

## Key files
- `app/Ai/Agents/DiaPempekAgent.php` — public agent (visitor-facing)
- `app/Ai/Agents/DiaPempekAdminAgent.php` — admin agent (tools enabled)
- `app/Http/Controllers/Ai/DiaPempekAIController.php` — publicChat/adminChat
- `config/ai.php` — provider config, uses env() directly (NOT config('services.*'))
- `app/Providers/AppServiceProvider.php` — global HTTP middleware patches SSE→JSON
- Routes: `POST /api/ai/chat` (public), `POST /admin/ai/chat` (admin)
- UI: `resources/views/admin/ai/index.blade.php` (uses page-header partial)

## Critical bug + fix
**Problem**: 9router tunnel gateway returns `Content-Type: text/event-stream`
even for non-streaming JSON requests, AND appends `data: [DONE]\n\n` SSE
terminator WITHOUT leading newline (body looks like
`..."cost":"0"}data: [DONE]\n\n`).
**Effect**: Laravel HTTP `->json()` returns null (despite Content-Type patch
alone), SDK throws `TypeError: validateTextResponse(): Argument #1 ($data)
must be of type array, null given`.

**Fix**: `Http::globalResponseMiddleware()` in `AppServiceProvider::boot()`:
1. Strip `data: [DONE]` SSE terminator from end of body if present (only
   when prefix still parses as JSON object/array)
2. Auto-close any unbalanced `{` / `[` after truncation
3. Override `Content-Type: text/event-stream` → `application/json`
4. Use `GuzzleHttp\Psr7\Utils::streamFor()` to create replacement PSR-7 stream

## Config gotcha (Laravel 12+)
- `config/ai.php` loads alphabetically BEFORE `config/services.php`
- Calling `config('services.chat.base_url')` inside `config/ai.php` returns NULL
- Fix: use `env()` directly inside config files for cross-config deps

## Verification pattern
```php
$resp = Http::withHeaders(['Authorization' => 'Bearer ' . env('LOCAL_AI_API_KEY')])
    ->post('https://rzgwipd.abc-tunnel.us/v1/chat/completions', [...]);
$d = $resp->json(); // must be array, not null
```

## Admin agent tools available
Tool methods to read/write DB: products, categories, orders, gallery,
articles, testimonials, faqs. Read first when debugging tool failures.
