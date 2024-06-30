<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract;

use Duyler\ActionBus\Build\ActionHandlerSubstitution;
use Duyler\ActionBus\Build\ActionResultSubstitution;

interface ActionSubstitutionInterface
{
    public function addResultSubstitutions(ActionResultSubstitution $actionResultSubstitution): void;

    public function addHandlerSubstitution(ActionHandlerSubstitution $actionHandlerSubstitution): void;

    public function isSubstituteHandler(string $actionId): bool;

    public function getSubstituteHandler(string $actionId): ActionHandlerSubstitution;

    public function isSubstituteResult(string $actionId): bool;

    public function getSubstituteResult(string $actionId): ActionResultSubstitution;
}
