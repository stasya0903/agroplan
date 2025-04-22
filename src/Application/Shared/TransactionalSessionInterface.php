<?php

namespace App\Application\Shared;

interface TransactionalSessionInterface
{
    /**
     * Run logic inside a transaction.
     *
     * @template T
     * @param callable(): T $fn
     * @return T
     */
    public function run(callable $fn): mixed;
}
