<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Bus\DoWhile;
use Duyler\EventBus\Bus\Rollback;
use Duyler\EventBus\Internal\Event\ThrowExceptionEvent;
use Ev;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class Runner
{
    public function __construct(
        private readonly DoWhile $doWhile,
        private readonly Rollback $rollback,
        private readonly EventDispatcherInterface $eventDispatcher,
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
        } finally {
            $this->doWhile->stop();
        }
    }
}
