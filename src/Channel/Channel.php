<?php

declare(strict_types=1);

namespace Duyler\EventBus\Channel;

final class Channel
{
    public const string DEFAULT_CHANNEL = 'common';

    private static Transfer $transfer;

    public function __construct(
        Transfer $transfer,
    ) {
        self::$transfer = $transfer;
    }

    public static function read(string $channel = 'common'): Listener
    {
        return new Listener($channel, self::$transfer);
    }

    public static function write(string $channel = 'common'): Message
    {
        return new Message($channel, self::$transfer);
    }
}
