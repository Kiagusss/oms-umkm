<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogImport;
use App\Models\Store;
use App\Services\Import\CatalogImportService;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;

class CatalogImportController extends Controller
{
    public function __construct(
        protected CatalogImportService $importService
    ) {}

    /**
     * Display the import landing page / initial form.
     */
    public function index()
    {
        $currentStore = TenantContext::getStore();
        $stores = Store::where('status', 'active')->get();
        $targetStoreId = $currentStore?->id ?? $stores->first()?->id;

        $recentImports = CatalogImport::where('store_id', $targetStoreId)
            ->latest()
            ->take(5)
            ->get();

        return view('admin.import.index', compact('currentStore', 'stores', 'targetStoreId', 'recentImports'));
    }

    /**
     * Validate the source URL or payload via AJAX / POST.
     */
    public function validateSource(Request $request)
    {
        $request->validate([
            'source' => 'nullable|string',
            'url' => 'nullable|string',
            'source_url' => 'nullable|string',
            'type' => 'nullable|string|in:mock,marketplace,csv,json,auto',
            'file' => 'nullable|file|max:10240',
        ]);

        $source = $request->input('source') ?? $request->input('url') ?? $request->input('source_url');
        $type = $request->input('type');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $source = $file->getRealPath();
            $ext = strtolower($file->getClientOriginalExtension());
            $type = in_array($ext, ['csv', 'json']) ? $ext : 'csv';
        }

        if (empty($source)) {
            return response()->json([
                'valid' => false,
                'message' => 'Silakan masukkan URL atau unggah file katalog.',
            ], 422);
        }

        $result = $this->importService->validateSource($source, $type === 'auto' ? null : $type);

        return response()->json($result);
    }

    /**
     * Generate preview of items to be imported.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'source' => 'nullable|string',
            'source_url' => 'nullable|string',
            'source_type' => 'nullable|string',
            'type' => 'nullable|string',
            'target_store_id' => 'nullable|integer',
            'file' => 'nullable|file|max:10240',
        ]);

        $type = $request->input('source_type') ?? $request->input('type') ?? 'mock';
        $source = $request->input('source_url') ?? $request->input('source');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('imports', 'local');
            $source = storage_path('app/' . $path);
            $ext = strtolower($file->getClientOriginalExtension());
            $type = in_array($ext, ['csv', 'json']) ? $ext : 'csv';
        }

        if ($type === 'mock' && empty($source)) {
            $source = 'mock';
        }

        if (empty($source)) {
            return redirect()->route('admin.import.index')->with('error', 'Sumber katalog tidak ditemukan.');
        }

        $targetStoreId = $request->input('target_store_id') ? (int) $request->input('target_store_id') : TenantContext::getStoreId();
        $targetStore = $targetStoreId ? Store::find($targetStoreId) : TenantContext::getStore();

        if (!$targetStore) {
            $targetStore = Store::defaultStore() ?? Store::first();
        }

        $previewData = $this->importService->preview(
            source: $source,
            type: $type === 'auto' ? null : $type,
            storeId: $targetStore?->id
        );

        $summary = [
            'total_products' => $previewData['total_items'],
            'new_products' => $previewData['will_create'],
            'duplicate_products' => $previewData['will_duplicate'],
            'total_categories' => $previewData['categories_count'],
            'total_variants' => array_sum(array_column($previewData['items'], 'variants_count')),
        ];

        $previewProducts = array_map(function($it) {
            return [
                'name' => $it['name'],
                'category' => $it['category_name'],
                'price' => $it['price'],
                'stock' => $it['stock'],
                'sku' => $it['sku'],
                'external_id' => $it['matched_id'] ?? null,
                'variants' => array_fill(0, $it['variants_count'], 'Variant'),
                'is_duplicate' => $it['is_duplicate'],
                'duplicate_reason' => $it['is_duplicate'] ? 'SKU atau Nama sudah ada' : '',
            ];
        }, $previewData['items']);

        return view('admin.import.preview', [
            'currentStore' => $targetStore,
            'targetStore' => $targetStore,
            'source' => $source,
            'sourceType' => $type,
            'sourceIdentifier' => $source === 'mock' ? 'Demo Mock Catalog (47 Produk)' : $source,
            'normalizedData' => $previewData['items'],
            'previewProducts' => $previewProducts,
            'summary' => $summary,
            'type' => $type,
            'preview' => $previewData,
        ]);
    }

    /**
     * Execute the catalog import.
     */
    public function execute(Request $request)
    {
        $request->validate([
            'source' => 'nullable|string',
            'source_identifier' => 'nullable|string',
            'source_type' => 'nullable|string',
            'type' => 'nullable|string',
            'target_store_id' => 'nullable|integer',
            'duplicate_strategy' => 'required|in:skip,update,create_new',
            'selected_indexes' => 'nullable|array',
        ]);

        $targetStoreId = $request->input('target_store_id') ? (int) $request->input('target_store_id') : TenantContext::getStoreId();
        $targetStore = $targetStoreId ? Store::find($targetStoreId) : TenantContext::getStore();

        if (!$targetStore) {
            return redirect()->route('admin.import.index')->with('error', 'Toko aktif tidak ditemukan.');
        }

        $source = $request->input('source') ?? $request->input('source_identifier') ?? 'mock';
        $type = $request->input('source_type') ?? $request->input('type') ?? 'mock';
        if ($source === 'Demo Mock Catalog (47 Produk)') {
            $source = 'mock';
        }

        $selected = $request->input('selected_indexes', []);
        $selected = array_map('intval', $selected);

        $import = $this->importService->executeImport(
            storeId: $targetStore->id,
            source: $source,
            type: $type,
            duplicateStrategy: $request->input('duplicate_strategy', 'skip'),
            selectedIndexes: $selected,
            userId: auth()->id()
        );

        return redirect()->route('admin.import.show', $import->id)
            ->with('success', "Proses impor selesai! {$import->imported_count} produk berhasil diproses.");
    }

    /**
     * Show import history list.
     */
    public function history()
    {
        $currentStore = TenantContext::getStore();
        $imports = CatalogImport::where('store_id', $currentStore?->id)
            ->with('user')
            ->latest()
            ->paginate(15);

        return view('admin.import.history', compact('currentStore', 'imports'));
    }

    /**
     * Show detail of an import execution.
     */
    public function show(int $id)
    {
        $currentStore = TenantContext::getStore();
        $import = CatalogImport::where('store_id', $currentStore?->id)
            ->with(['items', 'user'])
            ->findOrFail($id);

        return view('admin.import.show', compact('currentStore', 'import'));
    }
}
