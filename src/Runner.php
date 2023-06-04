<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Throwable;

readonly class Runner
{
    public function __construct(
        private DoWhile $doWhile,
        private Rollback $rollback
    ) {
    }

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        try {
            $this->doWhile->run();
        } catch (Throwable $exception) {
            $this->rollback->run();
            throw $exception;
        }
    }
}
