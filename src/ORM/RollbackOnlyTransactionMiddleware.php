<?php

declare(strict_types=1);

namespace League\Tactician\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Tactician\Middleware;
use Throwable;

class RollbackOnlyTransactionMiddleware implements Middleware
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Executes the given command and optionally returns a value
     *
     * @throws Throwable
     * @throws Exception
     */
    public function execute(object $command, callable $next): mixed
    {
        $this->entityManager->beginTransaction();

        try {
            $returnValue = $next($command);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();

            throw $e;
        } catch (Throwable $e) {
            $this->entityManager->rollback();

            throw $e;
        }

        return $returnValue;
    }
}
