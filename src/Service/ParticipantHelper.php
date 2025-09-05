<?php

namespace App\Service;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ParticipantHelper
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected RequestStack $request,
        protected ParticipantRepository $participantRepository,
    ) {
    }

  /**
   * @param Participant $participant
   */
    public function createParticipant(Participant $participant): void
    {
        $this->entityManager->persist($participant);
        $this->entityManager->flush();
    }

  /**
   * @param Participant $participant
   */
    public function createSessionParticipant(Participant $participant): void
    {
        $session = $this->request->getSession();
        $session->set('participant_id', $participant->getId());
    }

    public function getParticipant(int $participantId): Participant
    {
        $session = $this->request->getSession();
        $participantIdSession = $session->get('participant_id');

        if ($participantIdSession) {
            return $this->participantRepository->find($participantIdSession);
        }

        return $this->participantRepository->find($participantId);
    }
}
