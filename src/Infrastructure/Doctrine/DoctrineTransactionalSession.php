<?php

namespace App\Infrastructure\Doctrine;

use App\Application\Shared\TransactionalSessionInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineTransactionalSession implements TransactionalSessionInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function run(callable $fn): mixed
    {
        return $this->em->wrapInTransaction($fn); // Best with Doctrine >= 2.9
    }

}