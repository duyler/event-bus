<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Service\ResultService;
use Override;

class BusFacade implements BusInterface
{
    public function __construct(
        private Runner $runner,
        private ResultService $resultService,
    ) {}

    #[Override]
    public function run(): BusInterface
    {
        $this->runner->run();
        return $this;
    }

    #[Override]
    public function getResult(string $actionId): Result
    {
        return $this->resultService->getResult($actionId);
    }

    #[Override]
    public function resultIsExists(string $actionId): bool
    {
        return $this->resultService->resultIsExists($actionId);
    }
}
