<?php

declare(strict_types=1);

namespace Duyler\ActionBus;

use Duyler\ActionBus\Bus\DoWhile;
use Duyler\ActionBus\Bus\Rollback;
use Duyler\ActionBus\Internal\Event\ThrowExceptionEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class Runner
{
    public function __construct(
        private DoWhile $doWhile,
        private Rollback $rollback,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        try {
            $this->doWhile->run();
        } catch (Throwable $exception) {
            $this->rollback->run();
            $this->eventDispatcher->dispatch(new ThrowExceptionEvent($exception));
            throw $exception;
        }
    }
}
