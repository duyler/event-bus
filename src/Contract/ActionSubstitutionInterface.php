<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Dto\ActionHandlerSubstitution;

interface ActionSubstitutionInterface
{
    /** @param array<string, object> $substitutions  */
    public function addResultSubstitutions(string $actionId, array $substitutions): void;

    public function addHandlerSubstitution(ActionHandlerSubstitution $actionHandlerSubstitution): void;

    public function isSubstituteHandler(string $actionId): bool;

    public function getSubstituteHandler(string $actionId): ActionHandlerSubstitution;

    public function isSubstituteResult(string $actionId): bool;

    /** @return array<string, object> */
    public function getSubstituteResult(string $actionId): array;
}
