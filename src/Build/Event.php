<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
use InvalidArgumentException;
use JsonSerializable;
use Override;
use ReflectionClass;
use UnitEnum;

final readonly class Event implements JsonSerializable
{
    public string $id;

    public function __construct(
        string|UnitEnum $id,
        public ResultStatus $status = ResultStatus::Success,
        public ?string $type = null,
        public bool $immutable = true,
        public ?string $description = null,
    ) {
        $this->id = IdFormatter::toString($id) . IdFormatter::DELIMITER . $status->value;

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

    public static function success(string|UnitEnum $id, ?string $type = null): self
    {
        return new self($id, ResultStatus::Success, $type);
    }

    public static function fail(string|UnitEnum $id, ?string $type = null): self
    {
        return new self($id, ResultStatus::Fail, $type);
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'type' => $this->type,
            'immutable' => $this->immutable,
            'description' => $this->description,
        ];
    }
}
