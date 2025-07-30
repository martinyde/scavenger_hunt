<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Repository\RaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/participant')]
final class ParticipantController extends AbstractController
{
    public function __construct(protected RaceRepository $raceRepository)
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
    #[Route('/new', name: 'app_participant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participant->setRace($this->raceRepository->find(4));
            $entityManager->persist($participant);
            $entityManager->flush();

            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant/new.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

  /**
   * Show a specific participant.
   *
   * @param \App\Entity\Participant $participant
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
    #[Route('/{id}', name: 'app_participant_show', methods: ['GET'])]
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
    public function delete(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($participant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
    }
}
