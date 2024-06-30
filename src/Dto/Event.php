<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Dto;

use Duyler\ActionBus\Formatter\IdFormatter;
use UnitEnum;

final readonly class Event
{
    public string $id;

    public function __construct(
        string|UnitEnum $id,
        public ?object $data = null,
    ) {
        $this->id = IdFormatter::toString($id);
    }
}
