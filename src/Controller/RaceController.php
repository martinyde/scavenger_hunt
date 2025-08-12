<?php

namespace App\Controller;

use App\Entity\Race;
use App\Entity\ScavangerHunt;
use App\Form\RaceStartType;
use App\Form\RaceType;
use App\Form\TryType;
use App\Repository\ParticipantRepository;
use App\Repository\RaceRepository;
use App\Repository\TaskRepository;
use App\Service\RaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/race')]
final class RaceController extends AbstractController
{
  public function __construct(
    protected ParticipantRepository $participantRepository,
    protected TaskRepository        $taskRepository,
    protected RaceHelper            $raceHelper,
  )
  {
  }
  // Make personal (Userid)
    #[Route(name: 'app_race_index', methods: ['GET'])]
    public function index(RaceRepository $raceRepository): Response
    {
        return $this->render('race/index.html.twig', [
            'races' => $raceRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_race_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ScavangerHunt $scavangerHunt): Response
    {
        $race = new Race();
        $form = $this->createForm(RaceType::class, $race);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->raceHelper->createRace($race, $scavangerHunt);

            return $this->redirectToRoute('app_race_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('race/new.html.twig', [
            'race' => $race,
            'scavangerHunt' => $scavangerHunt,
            'form' => $form,
        ]);
    }

  #[Route('/form/try/{id}', name: 'app_form_try', methods: ['GET', 'POST'])]
  public function try(Request $request, Race $race): Response
  {
    $form = $this->createForm(TryType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $this->raceHelper->guessAccessKey($race, $form);

      return $this->redirectToRoute('app_race_show', ['id' => $race->getId(), 'uuid' => $race->getUuid()->toString()], Response::HTTP_SEE_OTHER);
    }

    return $this->render('race/try.html.twig', [
      'tryform' => $form,
    ]);
  }

    #[Route('/progress/{id}', name: 'app_race_progress', methods: ['GET', 'POST'])]
    public function progress(Request $request, Race $race): Response
    {
      $startRaceForm = $this->createForm(RaceStartType::class);
      $startRaceForm->handleRequest($request);

      if ($startRaceForm->isSubmitted() && $startRaceForm->isValid()) {
        $this->raceHelper->startRace($race);

        return $this->redirectToRoute('app_race_progress', ['id' => $race->getId()], Response::HTTP_SEE_OTHER);
      }

      return $this->render('race/progress.html.twig', [
        'race' => $race,
        'startRaceForm' => $startRaceForm,
        'timer' => [
          'secondsLeft' => $this->raceHelper->getSecondsLeft($race),
          'duration' => $race->getRaceDuration(),
          'raceState' => $race->isActive()
        ],
      ]);
    }

    #[Route('/{id}/{uuid}', name: 'app_race_show', methods: ['GET', 'POST'])]
    public function show(Race $race, string $uuid, Request $request): Response
    {
        if ($race->getUuid()->toString() !== $uuid) {
          throw $this->createAccessDeniedException();
        }

        $participant = $this->raceHelper->getParticipant();
        $tryForm = $this->createForm(TryType::class);
        $tryForm->handleRequest($request);

        if ($tryForm->isSubmitted() && $tryForm->isValid()) {
          return $this->redirectToRoute('app_race_show', ['id' => $race->getId(), 'uuid' => $uuid], Response::HTTP_SEE_OTHER);
        }

        return $this->render('race/show.html.twig', [
            'race' => $race,
            'tryform' => $tryForm,
            'participant' => $participant ?? null,
            'timer' => [
              'secondsLeft' => $this->raceHelper->getSecondsLeft($race),
              'duration' => $race->getRaceDuration(),
            ],
        ]);
    }

    #[Route('/{id}/edit', name: 'app_race_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Race $race, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RaceType::class, $race);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_race_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('race/edit.html.twig', [
            'race' => $race,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_race_delete', methods: ['POST'])]
    public function delete(Request $request, Race $race, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$race->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($race);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_race_index', [], Response::HTTP_SEE_OTHER);
    }
}
