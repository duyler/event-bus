<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Duyler\EventBus\Formatter\IdFormatter;
use UnitEnum;

final readonly class Event
{
    public string $id;

    public function __construct(
        string|UnitEnum $id,
        public ?string $contract = null,
    ) {
        $this->id = IdFormatter::toString($id);
    }
}
