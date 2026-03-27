<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Build\ActorHandlerSubstitution;
use Duyler\EventBus\Build\ActorResultSubstitution;

interface ActorSubstitutionInterface
{
    public function addResultSubstitutions(ActorResultSubstitution $actorResultSubstitution): void;

    public function addHandlerSubstitution(ActorHandlerSubstitution $actorHandlerSubstitution): void;

    public function isSubstituteHandler(string $actorId): bool;

    public function getSubstituteHandler(string $actorId): ActorHandlerSubstitution;

    public function isSubstituteResult(string $actorId): bool;

    public function getSubstituteResult(string $actorId): ActorResultSubstitution;
}
