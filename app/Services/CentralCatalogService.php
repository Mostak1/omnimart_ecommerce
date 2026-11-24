<?php

namespace App\Services;

use App\Actions\ImportGlobalItemAction;
use App\Models\Item;
use App\Models\Tenant\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CentralCatalogService
{
    public function __construct(
        protected ImportGlobalItemAction $importGlobalItemAction
    ) {}

    /**
     * Search items in the central catalog with optional category filter.
     * When running within a tenant context, annotates items with the store's current stock status.
     *
     * @param string $term
     * @param int|null $categoryId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $term = '', ?int $categoryId = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Item::with(['category:id,name,slug'])
            ->where('status', 1);

        $term = trim($term);
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('sku', 'LIKE', "%{$term}%")
                  ->orWhere('tags', 'LIKE', "%{$term}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        // If in tenant context, annotate each item with existing store stock status
        $this->annotateWithTenantStock($paginator->items());

        return $paginator;
    }

    /**
     * Get a specific central item with tenant stock details.
     *
     * @param int $id
     * @return Item
     */
    public function findCentralItem(int $id): Item
    {
        $item = Item::with(['category:id,name,slug', 'brand:id,name'])->findOrFail($id);

        $this->annotateWithTenantStock([$item]);

        return $item;
    }

    /**
     * Import a single global item into the current tenant's store.
     *
     * @param int|Item $item
     * @param array $customAttributes
     * @return Product
     */
    public function importToTenant(int|Item $item, array $customAttributes = []): Product
    {
        return $this->importGlobalItemAction->execute($item, $customAttributes);
    }

    /**
     * Bulk import multiple central catalog items into the tenant's store.
     *
     * @param array $items
     * @return array
     */
    public function bulkImportToTenant(array $items): array
    {
        $results = [
            'success_count' => 0,
            'failed_count' => 0,
            'imported' => [],
            'errors' => [],
        ];

        foreach ($items as $entry) {
            $itemId = $entry['item_id'] ?? $entry['id'] ?? null;
            if (!$itemId) {
                $results['failed_count']++;
                $results['errors'][] = ['error' => 'Missing item_id in entry', 'entry' => $entry];
                continue;
            }

            try {
                $product = $this->importToTenant((int)$itemId, $entry);
                $results['success_count']++;
                $results['imported'][] = [
                    'product_id' => $product->id,
                    'global_item_id' => $product->global_item_id,
                    'name' => $product->name,
                    'retail_price' => $product->retail_price,
                    'cost_price' => $product->cost_price,
                    'stock_quantity' => $product->stock_quantity,
                ];
            } catch (\Throwable $e) {
                $results['failed_count']++;
                $results['errors'][] = [
                    'item_id' => $itemId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Helper to annotate central item models with tenant's local product info.
     *
     * @param array $items
     * @return void
     */
    protected function annotateWithTenantStock(array $items): void
    {
        // Only run annotation if tenancy is initialized
        if (!function_exists('tenant') || !tenant()) {
            return;
        }

        $itemIds = collect($items)->pluck('id')->filter()->all();
        if (empty($itemIds)) {
            return;
        }

        $localProducts = Product::whereIn('global_item_id', $itemIds)
            ->get()
            ->keyBy('global_item_id');

        foreach ($items as $item) {
            $local = $localProducts->get($item->id);
            if ($local) {
                $item->setAttribute('is_imported', true);
                $item->setAttribute('tenant_product', [
                    'id' => $local->id,
                    'barcode' => $local->barcode,
                    'retail_price' => (float)$local->retail_price,
                    'cost_price' => (float)$local->cost_price,
                    'wholesale_price' => (float)$local->wholesale_price,
                    'stock_quantity' => (float)$local->stock_quantity,
                    'alert_quantity' => (float)$local->alert_quantity,
                    'unit' => $local->unit,
                    'status' => (bool)$local->status,
                ]);
            } else {
                $item->setAttribute('is_imported', false);
                $item->setAttribute('tenant_product', null);
            }
        }
    }
}

