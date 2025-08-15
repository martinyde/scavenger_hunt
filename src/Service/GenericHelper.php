<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Race;
use App\Entity\Task;
use App\Repository\ParticipantRepository;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RequestStack;

class GenericHelper {

  public function __construct(
    protected RequestStack $request,
    protected ParticipantRepository $participantRepository,
  ) {}

  /**
   * Confirm that a given Uuid matches a specific race.
   *
   * @param \App\Entity\Race|\App\Entity\Task $entity
   * @param string $uuid
   *
   * @return void
   */
  public function validateEntityUuid(Race|Task $entity, string $uuid): void {
    if ($entity->getUuid()->toString() !== $uuid) {
      throw new AccessDeniedException('UUID Mismatch');
    }
  }

  public function getCurrentParticipant(): ?Participant {
    $participantId = $this->request->getSession()->get('participant_id');

    return $participantId ? $this->participantRepository->find($participantId) : null;
  }
}