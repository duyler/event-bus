<?php

declare(strict_types=1);

namespace Duyler\EventBus\Actor\Context;

use Duyler\EventBus\Bus\ActorContainer;
use Duyler\EventBus\Formatter\IdFormatter;
use LogicException;
use UnitEnum;

use function array_key_exists;

final class FactoryContext extends BaseContext
{
    public function __construct(
        private readonly string $actorId,
        private readonly ActorContainer $actorContainer,

        /** @var array<string, mixed> */
        private $results = [],
    ) {
        parent::__construct($this->actorContainer);
    }

    public function getTypeById(string|UnitEnum $actorId): mixed
    {
        $id = IdFormatter::toString($actorId);
        if (false === array_key_exists($id, $this->results)) {
            throw new LogicException(
                'Type not defined with actor id ' . $id . ' for ' . $this->actorId . ' factory',
            );
        }

        return $this->results[$id];
    }
}
