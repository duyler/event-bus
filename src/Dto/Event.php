<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Dto;

use Duyler\ActionBus\Formatter\ActionIdFormatter;
use UnitEnum;

readonly class Event
{
    public string $id;

    public function __construct(
        string|UnitEnum $id,
        public ?object $data = null,
        public ?string $contract = null,
    ) {
        $this->id = ActionIdFormatter::toString($id);
    }
}
