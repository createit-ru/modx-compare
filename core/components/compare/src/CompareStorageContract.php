<?php

namespace Compare;

interface CompareStorageContract
{
    public function set(array $ids, string $list, string $contextKey);

    public function get(string $list, string $contextKey);

    public function total(string $contextKey);

    public function clean(string $list, string $contextKey);
}