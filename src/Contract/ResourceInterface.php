<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Socket;

interface ResourceInterface
{
    /**
     * @return Socket|resource|null Socket resource or null if unavailable
     */
    public function getResource(): mixed;
}
