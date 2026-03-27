<?php

declare(strict_types=1);

namespace Duyler\EventBus\Formatter;

use UnitEnum;

use function array_search;
use function is_string;

final class IdFormatter
{
    public const string DELIMITER = '::';

    /** @var array<string, UnitEnum> */
    private static array $idMap = [];

    public static function toString(string|UnitEnum $id): string
    {
        if (is_string($id)) {
            return $id;
        }

        $actorId = array_search($id, self::$idMap, true);

        if (is_string($actorId)) {
            return $actorId;
        }

        $actorId = $id::class . self::DELIMITER . $id->name;
        self::$idMap[$actorId] = $id;
        return $actorId;
    }

    public static function reverse(string $id): string|UnitEnum
    {
        return self::$idMap[$id] ?? $id;
    }

    public static function remove(string $id): void
    {
        unset(self::$idMap[$id]);
    }

    public static function fromEventId(string $eventId): string
    {
        $lastDelimiterPos = strrpos($eventId, self::DELIMITER);

        if (false === $lastDelimiterPos) {
            return $eventId;
        }

        return substr($eventId, 0, $lastDelimiterPos);
    }

    public function finalize(): void
    {
        self::$idMap = [];
    }
}
