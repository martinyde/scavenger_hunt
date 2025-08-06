<?php

namespace App\Controller;

use App\Entity\Race;
use App\Form\RaceStartType;
use App\Form\RaceType;
use App\Form\TryType;
use App\Repository\ParticipantRepository;
use App\Repository\RaceRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/race')]
final class RaceController extends AbstractController
{
  public function __construct(
    protected ParticipantRepository $participantRepository,
    protected TaskRepository $taskRepository,
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

    // remove?
    #[Route('/new', name: 'app_race_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $race = new Race();
        $form = $this->createForm(RaceType::class, $race);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $race->setActive(false);
            $entityManager->persist($race);
            $entityManager->flush();

            return $this->redirectToRoute('app_race_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('race/new.html.twig', [
            'race' => $race,
            'form' => $form,
        ]);
    }

  #[Route('/form/try/{id}', name: 'app_form_try', methods: ['GET', 'POST'])]
  public function try(Request $request, Race $race, EntityManagerInterface $entityManager): Response
  {
    $form = $this->createForm(TryType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $session = $request->getSession();
      $activeUserId = $session->get('participant_id');
      $participant = $activeUserId ? $this->participantRepository->find($activeUserId) : null;

      $raceScavengerHuntId = $race->getScavengerHunt()->getId();
      $this->participantRepository->find($activeUserId);
      $tasks = $this->taskRepository->findBy(['scavangerHunt' => $raceScavengerHuntId]);
      foreach ($tasks as $task) {
        if ($form->get('try')->getData() === $task->getPassKey() &&
          !$participant->getProgressTaskEntry()->contains($task)
        ) {
          $participant->setProgressEntryCount($participant->getProgressEntryCount() + 1);
          $participant->addProgressTaskEntry($task);
          $entityManager->persist($participant);
          $entityManager->flush();
        }
      }

      return $this->redirectToRoute('app_race_show', ['id' => $race->getId(), 'uuid' => $race->getUuid()->toString()], Response::HTTP_SEE_OTHER);
    }

    return $this->render('race/try.html.twig', [
      'tryform' => $form,
    ]);
  }

    #[Route('/progress/{id}', name: 'app_race_progress', methods: ['GET', 'POST'])]
    public function progress(Request $request, Race $race, EntityManagerInterface $entityManager, HubInterface $hub): Response
    {

      $startRaceForm = $this->createForm(RaceStartType::class);
      $startRaceForm->handleRequest($request);

      if ($startRaceForm->isSubmitted() && $startRaceForm->isValid()) {
        $race->setTimeStart(new DatePoint());
        $race->setActive(true);
        $entityManager->flush();

        $tryform = $this->createForm(TryType::class);

        $update = new Update(
          'race_state_changed',
          $this->renderView('broadcast/Try.form.stream.html.twig', [
            'race' => $race,
            'tryform' => $tryform,
          ])
        );

        $hub->publish($update);

        return $this->redirectToRoute('app_race_progress', ['id' => $race->getId()], Response::HTTP_SEE_OTHER);
      }

      $now = new DatePoint();

      return $this->render('race/progress.html.twig', [
        'race' => $race,
        'startRaceForm' => $startRaceForm,
        'timer' => [
          'secondsLeft' => $race->getTimeStart()->modify('+' . $race->getRaceDuration() . ' seconds')->getTimestamp() - $now->getTimestamp(),
          'duration' => $race->getRaceDuration(),
          'raceState' => $race->isActive()
        ],
      ]);
    }

    #[Route('/{id}/{uuid}', name: 'app_race_show', methods: ['GET', 'POST'])]
    public function show(Race $race, string $uuid, Request $request,
    ): Response
    {
        $session = $request->getSession();
        $activeUserId = $session->get('participant_id');
        $participant = $activeUserId ? $this->participantRepository->find($activeUserId) : null;
        if ($race->getUuid()->toString() !== $uuid) {
          throw $this->createAccessDeniedException();
        }
        $tryform = $this->createForm(TryType::class);
        $tryform->handleRequest($request);

        if ($tryform->isSubmitted() && $tryform->isValid()) {
          return $this->redirectToRoute('app_race_show', ['id' => $race->getId(), 'uuid' => $uuid], Response::HTTP_SEE_OTHER);
        }

        $now = new DatePoint();

        return $this->render('race/show.html.twig', [
            'race' => $race,
            'tryform' => $tryform,
            'participant' => $participant ?? null,
            'timer' => [
              'secondsLeft' => $race->getTimeStart()->modify('+' . $race->getRaceDuration() . ' seconds')->getTimestamp() - $now->getTimestamp(),
              'timeLeft' => $now->diff($race->getTimeStart()->modify('+' . $race->getRaceDuration() . ' seconds'))->format('%H:%I:%S'),
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
