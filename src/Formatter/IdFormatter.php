<?php

declare(strict_types=1);

namespace Duyler\EventBus\Formatter;

use Duyler\DI\Attribute\Finalize;
use UnitEnum;

#[Finalize]
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

        $actionId = array_search($id, self::$idMap, true);

        if (is_string($actionId)) {
            return $actionId;
        }

        $actionId = $id::class . self::DELIMITER . $id->name;
        self::$idMap[$actionId] = $id;
        return $actionId;
    }

    public static function reverse(string $id): string|UnitEnum
    {
        return self::$idMap[$id] ?? $id;
    }

    public static function remove(string $id): void
    {
        unset(self::$idMap[$id]);
    }

    public function finalize(): void
    {
        self::$idMap = [];
    }
}
