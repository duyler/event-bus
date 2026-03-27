<?php

declare(strict_types=1);

namespace Duyler\EventBus\Actor;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Build\ActorHandlerSubstitution;
use Duyler\EventBus\Build\ActorResultSubstitution;
use Duyler\EventBus\Contract\ActorSubstitutionInterface;
use Override;

use function array_key_exists;

#[Finalize(method: 'reset')]
class ActorSubstitution implements ActorSubstitutionInterface
{
    /** @var array<string, ActorResultSubstitution> */
    private array $requiredResultSubstitutions = [];

    /** @var array<string, ActorHandlerSubstitution> */
    private array $handlerSubstitutions = [];

    #[Override]
    public function addResultSubstitutions(ActorResultSubstitution $actorResultSubstitution): void
    {
        $this->requiredResultSubstitutions[$actorResultSubstitution->actorId] = $actorResultSubstitution;
    }

    #[Override]
    public function addHandlerSubstitution(ActorHandlerSubstitution $actorHandlerSubstitution): void
    {
        $this->handlerSubstitutions[$actorHandlerSubstitution->actorId] = $actorHandlerSubstitution;
    }

    #[Override]
    public function isSubstituteHandler(string $actorId): bool
    {
        return array_key_exists($actorId, $this->handlerSubstitutions);
    }

    #[Override]
    public function getSubstituteHandler(string $actorId): ActorHandlerSubstitution
    {
        return $this->handlerSubstitutions[$actorId];
    }

    #[Override]
    public function isSubstituteResult(string $actorId): bool
    {
        return array_key_exists($actorId, $this->requiredResultSubstitutions);
    }

    #[Override]
    public function getSubstituteResult(string $actorId): ActorResultSubstitution
    {
        return $this->requiredResultSubstitutions[$actorId];
    }

    public function reset(): void
    {
        $this->handlerSubstitutions = [];
        $this->requiredResultSubstitutions = [];
    }
}
