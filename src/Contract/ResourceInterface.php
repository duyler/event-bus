<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

interface ResourceInterface
{
    /**
     * @return resource|null Socket resource or null if unavailable
     */
    public function getResource(): mixed;
}
