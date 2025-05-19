<?php

declare(strict_types=1);

namespace Duyler\EventBus\Channel;

use Duyler\EventBus\Formatter\IdFormatter;
use Fiber;
use UnitEnum;

final class Listener
{
    public function __construct(
        private string $channel,
        private Transfer $transfer,
    ) {}

    public function listen(string|UnitEnum $tag): mixed
    {
        $tag = IdFormatter::toString($tag);

        $callback = function () use ($tag): null|false|Message {
            if ($this->transfer->has($this->channel, $tag)) {
                return $this->transfer->get($this->channel, $tag);
            }

            if (false === $this->transfer->isValid()) {
                return null;
            }

            return false;
        };

        do {
            /** @var Message|false|null $data */
            $data = Fiber::suspend($callback);
        } while (false === $data);

        return $data?->getPayload();
    }

    public function get(string $tag): mixed
    {
        $tag = IdFormatter::toString($tag);

        $callback =  function () use ($tag): null|Message {
            if ($this->transfer->has($this->channel, $tag)) {
                return $this->transfer->get($this->channel, $tag);
            }

            return null;
        };

        /** @var Message|null $data */
        $data = Fiber::suspend($callback);
        return $data?->getPayload();
    }
}
