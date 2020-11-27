<?php

declare(strict_types=1);

namespace Jine\EventBus\Contract;

use Jine\EventBus\Dto\Result;
use Jine\Contracts\Service\ActionInterface;

interface HandlerInterface
{
    public function run(ActionInterface $action): Result;

    public function getClass(): string;

    public function getClassMap(): array;
}
