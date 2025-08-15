<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Race;
use App\Form\ParticipantType;
use App\Form\RaceJoinType;
use App\Repository\ParticipantRepository;
use App\Repository\RaceRepository;
use App\Service\GenericHelper;
use App\Service\ParticipantHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\UuidV7;

#[Route('/participant')]
final class ParticipantController extends AbstractController
{
    public function __construct(
      protected RaceRepository $raceRepository,
      protected ParticipantRepository $participantRepository,
      protected ParticipantHelper $participantHelper,
      protected GenericHelper $genericHelper,
    )
    {
    }

  /**
   * List all participants that exist.
   *
   * @param \App\Repository\ParticipantRepository $participantRepository
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
    #[Route(name: 'app_participant_index', methods: ['GET'])]
    #[IsGranted('access_admin')]
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->render('participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
        ]);
    }

  /**
   * Add a new participant.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Doctrine\ORM\EntityManagerInterface $entityManager
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
    #[Route('/new/{race}', name: 'app_participant_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->participantHelper->createParticipant($participant);

            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant/new.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

  /**
   * Join a race as participant.
   *
   * @param \App\Entity\Race $race
   * @param string $raceUuid
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Doctrine\ORM\EntityManagerInterface $entityManager
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  #[Route('/add/{race}/{raceUuid}', name: 'app_participant_add', methods: ['GET', 'POST'])]
  public function add(Race $race, string $raceUuid, Request $request, EntityManagerInterface $entityManager): Response
  {
    $this->genericHelper->validateEntityUuid($race, $raceUuid);
    $participant = new Participant();
    $form = $this->createForm(RaceJoinType::class, $participant);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $participant->setUuid(new UuidV7());
      $participant->setRace($this->raceRepository->find($race->getId()));

      $entityManager->persist($participant);
      $entityManager->flush();

      $this->participantHelper->createSessionParticipant($participant);
      return $this->redirectToRoute('app_race_show', ['id' => $race->getId(), 'uuid' => $race->getUuid(), 'participantUuid' => $participant->getUuid()], Response::HTTP_SEE_OTHER);
    }

    return $this->render('participant/new.html.twig', [
      'participant' => $participant,
      'form' => $form,
    ]);
  }

  #[Route('/join/{race}/{raceUuid}/{participant}', name: 'app_participant_join', methods: ['GET'])]
  public function join(Race $race, string $raceUuid, Participant $participant): Response
  {
    $this->genericHelper->validateEntityUuid($race, $raceUuid);
    $this->participantHelper->createSessionParticipant($participant);

    return $this->redirectToRoute('app_race_show', ['id' => $race->getId(), 'uuid' => $race->getUuid(), 'participantUuid' => $participant->getUuid()], Response::HTTP_SEE_OTHER);
  }

  /**
   * Show a specific participant.
   *
   * @param \App\Entity\Participant $participant
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
    #[Route('/{id}', name: 'app_participant_show', methods: ['GET'])]
    #[IsGranted('access_admin')]
    public function show(Participant $participant): Response
    {
        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

  /**
   * Change an existing participant.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \App\Entity\Participant $participant
   * @param \Doctrine\ORM\EntityManagerInterface $entityManager
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
    #[Route('/{id}/edit', name: 'app_participant_edit', methods: ['GET', 'POST'])]
    #[IsGranted('access_admin')]
    public function edit(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    /**
     * Delete a specific participant.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Participant $participant
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
      #[Route('/{id}', name: 'app_participant_delete', methods: ['POST'])]
      #[IsGranted('access_admin')]
      public function delete(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
      {
          if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->getPayload()->getString('_token'))) {
              $entityManager->remove($participant);
              $entityManager->flush();
          }

          return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
      }
}
