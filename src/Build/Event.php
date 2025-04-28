<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Duyler\EventBus\Formatter\IdFormatter;
use InvalidArgumentException;
use ReflectionClass;
use UnitEnum;

final readonly class Event
{
    public string $id;

    public function __construct(
        string|UnitEnum $id,
        public ?string $type = null,
        public bool $immutable = true,
    ) {
        $this->id = IdFormatter::toString($id);

        if ($this->immutable) {
            if (null !== $this->type) {
                if (interface_exists($this->type)) {
                    throw new InvalidArgumentException('Type of ' . $this->type . ' it should not be an interface');
                }

                /** @var class-string $type */
                $reflectionContract = new ReflectionClass($type);
                if (false === $reflectionContract->isReadOnly()) {
                    throw new InvalidArgumentException('Type ' . $this->type . ' must be read only class');
                }
            }
        }
    }
}
