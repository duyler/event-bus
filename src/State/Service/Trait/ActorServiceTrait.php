<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Build\Actor as ExternalActor;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Bus\Actor as InternalActor;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Service\ActorService;
use UnitEnum;

/**
 * @property ActorService $actorService
 */
trait ActorServiceTrait
{
    public function addActor(ExternalActor $actor): void
    {
        $internalActor = InternalActor::fromExternal($actor);
        $this->actorService->addDynamicActor($internalActor);
    }

    public function doActor(ExternalActor $actor, string $scope = 'common'): void
    {
        $this->actorService->doDynamicActor(InternalActor::fromExternal($actor), $scope);
    }

    public function doExistsActor(string|UnitEnum $actorId, string $scope = 'common'): void
    {
        $this->actorService->doExistsActor(IdFormatter::toString($actorId), $scope);
    }

    public function actorIsExists(string|UnitEnum $actorId): bool
    {
        return $this->actorService->actorIsExists(IdFormatter::toString($actorId));
    }

    public function removeActor(string|UnitEnum $actorId): void
    {
        $this->actorService->removeActor(IdFormatter::toString($actorId));
    }

    /** @return array<string, ExternalActor> */
    public function getByType(string $type): array
    {
        $externalByType = [];

        foreach ($this->actorService->getByType($type) as $actor) {
            $externalByType[$actor->getId()] = ExternalActor::fromInternal($actor);
        }

        return $externalByType;
    }

    public function getById(string|UnitEnum $actorId): ExternalActor
    {
        $internal = $this->actorService->getById(IdFormatter::toString($actorId));
        return ExternalActor::fromInternal($internal);
    }

    public function addSharedService(SharedService $sharedService): void
    {
        $this->actorService->addSharedService($sharedService);
    }
}
