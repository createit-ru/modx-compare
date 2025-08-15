<?php

namespace Compare\Services;

use Compare\CompareStorageContract;
use Compare\Model\ComparedProduct;
use modX;

class CompareService
{
    private modX $modx;

    private \miniShop2 $miniShop2;

    private CompareStorageContract $storage;

    private ?array $ms2Options = null;

    private string $contextKey;

    public function __construct(modX &$modx, CompareStorageContract $storage)
    {
        $this->modx = &$modx;
        $this->storage = $storage;
        $this->contextKey = $this->modx->context ? $this->modx->context->key : 'web';

        // load miniShop2
        $service = $this->modx->getService('miniShop2');
        if (!$service instanceof \miniShop2) {
            throw new \RuntimeException('Could not load miniShop2 service');
        }
        $this->miniShop2 = $service;
    }

    /**
     * Loads the state of all add to compare buttons
     *
     * @param array $data
     * @return array|string
     */
    public function load(array $data = []): array
    {
        $result = [];

        if (isset($data['ids'])) {
            foreach ($data['ids'] as $v) {
                $id = $v['id'];
                $list = $v['list'];
                $listIds = $this->storage->get($list, $this->contextKey);

                $result[$id][$list] = [
                    'id' => $id,
                    'list' => $list,
                    'added' => in_array($id, $listIds),
                    'count' => count($listIds)
                ];
            }
        }
        return $result;
    }

    /**
     * Add to compare button click handler
     *
     * @param array $data
     * @return array
     */
    public function toggle(array $data = []): array
    {
        $result = [];

        if (isset($data['id']) && isset($data['list'])) {
            $id = intval($data['id']);
            $list = $data['list'];
            $listIds = $this->storage->get($list, $this->contextKey);
            if (in_array($id, $listIds)) {
                // remove
                $listIds = array_diff($listIds, [$id]);
            } else {
                // add
                $listIds = array_merge($listIds, [$id]);
            }
            $this->storage->set($listIds, $list, $this->contextKey);
            $listIds = $this->storage->get($list, $this->contextKey);

            $result[$id][$list] = [
                'id' => $id,
                'list' => $list,
                'added' => in_array($id, $listIds),
                'count' => count($listIds)
            ];
        }
        return $result;
    }

    /**
     * Remove handler
     *
     * @param array $data
     * @return array
     */
    public function remove(array $data = []): array
    {
        $result = [];

        if (isset($data['id']) && isset($data['list'])) {
            $id = intval($data['id']);
            $list = $data['list'];
            $listIds = $this->storage->get($list, $this->contextKey);

            // remove
            $listIds = array_diff($listIds, [$id]);

            $this->storage->set($listIds, $list, $this->contextKey);
        }
        return $result;
    }

    /**
     * Clean handler
     *
     * @param array $data
     * @return array
     */
    public function clean(array $data = []): array
    {
        $result = [];

        if (isset($data['list'])) {
            $list = $data['list'];
            $this->storage->clean($list, $this->contextKey);
        }
        return $result;
    }

    /**
     * Mini handler
     *
     * @param array $data
     * @return array
     */
    public function mini(array $data = []): array
    {
        $count = $this->storage->total($this->contextKey);
        return ['count' => $count];
    }

    public function compare(string $list, array $fields, array $priceFields, array $weightFields, array $best): array
    {
        $ids = $this->storage->get($list, $this->contextKey);

        $data = [
            'products' => [],
            'list' => $list,
        ];
        if (count($ids) > 0) {

            $products = $this->getCompareProducts($ids);
            $vendors = $this->getVendors();

            $lang = $this->modx->getOption('cultureKey');
            $this->modx->lexicon->load($lang . ':minishop2:product');


            $data['fields'] = [];
            foreach ($fields as $field) {
                $atLeastOne = false;
                $fieldValues = [];
                $formattedValues = [];
                /** @var ComparedProduct $product */
                foreach ($products as $product) {
                    $rawValue = $product->getFieldValue($field);

                    if (in_array($field, $weightFields)) {
                        $formattedValue = $this->formatWeight($rawValue, '-');
                    } elseif (in_array($field, $priceFields)) {
                        $formattedValue = $this->formatPrice($rawValue, '-');
                    } elseif ($field == 'vendor') {
                        $formattedValue = array_key_exists($rawValue, $vendors) ? $vendors[$rawValue] : $rawValue;
                    } else {
                        $formattedValue = is_array($rawValue) ? implode(', ', $rawValue) : (string)$rawValue;
                    }

                    if ($formattedValue !== false && $formattedValue !== '') {
                        $atLeastOne = true;
                    }
                    $fieldValues[] = [
                        'best' => false,
                        'raw' => $rawValue,
                        'formatted' => $formattedValue,
                    ];
                    $formattedValues[] = $formattedValue;
                }
                if (array_key_exists($field, $best)) {
                    $this->highlightBest($fieldValues, $best[$field]);
                }

                if ($atLeastOne) {
                    $data['fields'][$field] = [
                        'title' => $this->getFieldTitle($field),
                        'different' => count(array_unique($formattedValues)) > 1,
                        'values' => $fieldValues,
                    ];
                }
            }

            foreach ($products as $product) {
                $productArray = $product->toArray();
                foreach ($priceFields as $pf) {
                    if (array_key_exists($pf, $productArray)) {
                        $productArray[$pf] = $this->formatPrice($productArray[$pf]);
                    }
                }
                $data['products'][] = $productArray;
            }
        }
        return $data;
    }

    private function highlightBest(array &$fieldValues, string $method = 'max'): void
    {
        // Извлекаем ненулевые значения
        $nonZeroRaws = array_filter(array_column($fieldValues, 'raw'), fn($v) => $v != 0);

        // Выходим если нет значений для сравнения
        if (empty($nonZeroRaws)) {
            return;
        }

        // Выходим если все ненулевые значения одинаковы (нечего выделять)
        if (count(array_unique($nonZeroRaws)) === 1) {
            return;
        }

        $targetValue = $method === 'max' ? max($nonZeroRaws) : min($nonZeroRaws);

        // Устанавливаем best=true только для целевых значений
        foreach ($fieldValues as &$item) {
            if (isset($item['raw'])) {
                $item['best'] = $item['raw'] == $targetValue;
            }
        }
        unset($item);
    }

    private function getCompareProducts(array $ids): array
    {
        $query = $this->modx->newQuery('msProduct');
        $query->where([
            'class_key' => 'msProduct',
            'id:IN' => $ids,
            'published' => true,
            'deleted' => false
        ]);
        $query->sortby("FIELD(msProduct.id, '" . implode(',', $ids) . "' )");
        $msProducts = $this->modx->getIterator('msProduct', $query);

        $result = [];
        foreach ($msProducts as $msProduct) {
            $result[] = new ComparedProduct($msProduct);
        }

        return $result;
    }

    private function getVendors(): array
    {
        $vendors = $this->modx->getIterator('msVendor');
        $result = [];
        foreach ($vendors as $vendor) {
            $result[$vendor->get('id')] = $vendor->get('name');
        }
        return $result;
    }

    private function ms2Options(): array
    {
        $q = $this->modx->newQuery('msOption');
        $result = [];
        $options = $this->modx->getIterator('msOption', $q);
        foreach ($options as $option) {
            $result[$option->get('key')] = $option->toArray();
        }
        return $result;
    }

    private function formatWeight(float $weight, ?string $default = null): ?string
    {
        if ($weight == 0 && $default != null) {
            return $default;
        }
        return $this->miniShop2->formatWeight($weight);
    }

    private function formatPrice(float $price, ?string $default = null): ?string
    {
        if ($price == 0 && $default != null) {
            return $default;
        }
        return $this->miniShop2->formatPrice($price);
    }

    private function getFieldTitle($field): string
    {
        if (is_null($this->ms2Options)) {
            $this->ms2Options = $this->ms2Options();
        }
        if (array_key_exists($field, $this->ms2Options)) {
            return $this->ms2Options[$field]['caption'];
        }
        return $this->modx->lexicon('ms2_product_' . $field);
    }
}