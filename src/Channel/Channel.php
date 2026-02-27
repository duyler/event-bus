<?php

declare(strict_types=1);

namespace Duyler\EventBus\Channel;

use Closure;
use Fiber;
use Override;

final class Channel implements ChannelInterface
{
    public const string DEFAULT_CHANNEL = "common";

    private static Transfer $transfer;

    public function __construct(
        Transfer $transfer,
        private readonly string $name = self::DEFAULT_CHANNEL,
    ) {
        self::$transfer = $transfer;
    }

    public static function open(
        string $name = self::DEFAULT_CHANNEL,
    ): ChannelInterface {
        return new Channel(self::$transfer, $name);
    }

    #[Override]
    public function recv(): mixed
    {
        $transfer = self::$transfer;

        /**
         * @var Closure $callback
         */
        $callback = Fiber::suspend(fn(): Closure => function (Transfer $transfer): mixed {
            while ($transfer->isValid()) {
                if ($transfer->has($this->name)) {
                    return $transfer->get($this->name);
                }
                Fiber::suspend();
            }
            return null;
        });

        return $callback($transfer);
    }

    #[Override]
    public function send(mixed $value): void
    {
        self::$transfer->push($this->name, $value);
        Fiber::suspend();
    }
}
