<?php

declare(strict_types=1);

namespace Duyler\EventBus\Channel;

interface ChannelInterface
{
    public function recv(): mixed;

    public function send(mixed $value): void;
}
