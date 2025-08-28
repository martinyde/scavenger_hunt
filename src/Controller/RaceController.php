<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Race;
use App\Entity\ScavengerHunt;
use App\Form\RaceStartType;
use App\Form\RaceType;
use App\Form\TryType;
use App\Repository\ParticipantRepository;
use App\Repository\RaceRepository;
use App\Repository\ScavengerHuntRepository;
use App\Repository\TaskRepository;
use App\Service\GenericHelper;
use App\Service\RaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/race')]
final class RaceController extends AbstractController
{
  public function __construct(
    protected ParticipantRepository $participantRepository,
    protected TaskRepository        $taskRepository,
    protected RaceHelper            $raceHelper,
    protected GenericHelper         $genericHelper,
    protected ScavengerHuntRepository $scavengerHuntRepository,
  )
  {
  }
  // Make personal (Userid)
    #[Route(name: 'app_race_index', methods: ['GET'])]
    #[IsGranted('view')]
    public function index(RaceRepository $raceRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $scavengerHunts = $this->scavengerHuntRepository->findBy(['user' => $user->getId()]);
        $races = [];
        foreach ($scavengerHunts as $scavengerHunt) {
          $races = [...$races, ...$scavengerHunt->getRaces()->toArray()];
        }

        return $this->render('race/index.html.twig', [
            'races' => $races,
        ]);
    }

    #[Route('/new/{id}', name: 'app_race_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ScavengerHunt $scavengerHunt): Response
    {
        $race = new Race();
        $form = $this->createForm(RaceType::class, $race);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->raceHelper->createRace($race, $scavengerHunt);

            return $this->redirectToRoute('app_race_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('race/new.html.twig', [
            'race' => $race,
            'scavengerHunt' => $scavengerHunt,
            'form' => $form,
        ]);
    }

    #[Route('/form/try/{id}', name: 'app_form_try', methods: ['GET', 'POST'])]
    public function try(Request $request, Race $race): Response
    {
      $participant = $this->genericHelper->getCurrentParticipant();

      if (empty($participant)) {
        throw new AccessDeniedHttpException(
          'Not a participant'
        );
      }
      $form = $this->createForm(TryType::class, ['request' => $request]);
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        $this->raceHelper->guessAccessKey($race, $form, $participant->getUuid());

        return $this->redirectToRoute('app_race_show', ['id' => $race->getId(), 'uuid' => $race->getUuid()->toString(), 'participantUuid' => $participant->getUuid()], Response::HTTP_SEE_OTHER);
      }

      return $this->render('race/try.html.twig', [
        'participant' => $participant,
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

    #[Route('/{id}/{uuid}/{participantUuid}', name: 'app_race_show', methods: ['GET', 'POST'])]
    public function show(Race $race, string $uuid, Request $request, ?string $participantUuid = null): Response
    {
        $this->genericHelper->validateEntityUuid($race, $uuid);
        $participant = $this->genericHelper->getCurrentParticipant($participantUuid);

        $tryForm = $this->createForm(TryType::class, ['request' => $request]);
        $tryForm->handleRequest($request);

        if ($tryForm->isSubmitted() && $tryForm->isValid()) {
          return $this->redirectToRoute('app_race_show', ['id' => $race->getId(), 'uuid' => $uuid, 'participantUuid' => $participant->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('race/show.html.twig', [
            'race' => $race,
            'raceHelper' => $this->raceHelper,
            'tryform' => $tryForm,
            'participant' => $participant,
            'timer' => [
              'secondsLeft' => $this->raceHelper->getSecondsLeft($race),
              'duration' => $race->getRaceDuration(),
            ],
        ]);
    }

    #[Route('/participant-finish/{participant}/{participantUuid}/{race}', name: 'app_race_participant_finish', methods: ['GET'])]
    public function participantFinish(Participant $participant, string $participantUuid, Race $race, EntityManagerInterface $entityManager): Response
    {
      $this->genericHelper->validateEntityUuid($participant, $participantUuid);
      $this->genericHelper->createHighscore($participant);
      $entityManager->flush();
      return $this->redirectToRoute('app_highscore_race_index', ['race' => $race->getId()], Response::HTTP_SEE_OTHER);
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
