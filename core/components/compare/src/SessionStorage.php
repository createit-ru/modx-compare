<?php

namespace Compare;

/**
 * Хранилище для объектов, находящихся в сравнении
 */
class SessionStorage implements CompareStorageContract
{
    private int $max = 100;

    public function set(array $ids, string $list = 'default', string $contextKey = 'web'): void
    {
        $ids = array_slice($ids, -$this->max);
        $_SESSION['compare'][$contextKey][$list] = array(
            'ids' => $ids,
        );
    }

    public function get(string $list = 'default', string $contextKey = 'web'): array
    {
        return !empty($_SESSION['compare'][$contextKey][$list])
            ? $_SESSION['compare'][$contextKey][$list]['ids']
            : [];
    }

    public function total(string $contextKey = 'web'): int
    {
        $count = 0;
        if (!empty($_SESSION['compare'][$contextKey])) {
            foreach ($_SESSION['compare'][$contextKey] as $list) {
                $count += count($list['ids']);
            }
        }

        return $count;
    }

    public function clean(string $list = 'default', string $contextKey = 'web'): void
    {
        unset($_SESSION['compare'][$contextKey][$list]);
    }
}