<?php

namespace App\Controller;

use App\Entity\Highscore;
use App\Entity\Race;
use App\Entity\ScavangerHunt;
use App\Form\HighscoreType;
use App\Repository\HighscoreRepository;
use App\Service\GenericHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/highscore')]
final class HighscoreController extends AbstractController
{
    public function __construct(
      protected GenericHelper $genericHelper,
    )
    {
    }

    #[Route(name: 'app_highscore_index', methods: ['GET'])]
    public function index(HighscoreRepository $highscoreRepository): Response
    {
      $a =1;
        return $this->render('highscore/index.html.twig', [
            'highscores' => $highscoreRepository->findAll(),
        ]);
    }

    #[Route('/race/{race}', name: 'app_highscore_race_index', methods: ['GET'])]
    public function raceIndex(HighscoreRepository $highscoreRepository, Race $race): Response
    {
      return $this->render('highscore/index.html.twig', [
        'highscores' => $highscoreRepository->getRaceHighScores($race),
      ]);
    }

    #[Route('/scavanger_hunt/{scavangerHunt}', name: 'app_highscore_scavenger_hunt_index', methods: ['GET'])]
    public function scavengerHuntIndex(HighscoreRepository $highscoreRepository, ScavangerHunt $scavangerHunt): Response
    {
      return $this->render('highscore/index.html.twig', [
        'highscores' => $highscoreRepository->getScavengerHuntHighScores($scavangerHunt),
      ]);
    }

    #[IsGranted('access_admin')]
    #[Route('/new', name: 'app_highscore_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $highscore = new Highscore();
        $form = $this->createForm(HighscoreType::class, $highscore);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($highscore);
            $entityManager->flush();

            return $this->redirectToRoute('app_highscore_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('highscore/new.html.twig', [
            'highscore' => $highscore,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_highscore_show', methods: ['GET'])]
    public function show(Highscore $highscore): Response
    {
        return $this->render('highscore/show.html.twig', [
            'highscore' => $highscore,
        ]);
    }

    #[IsGranted('access_admin')]
    #[Route('/{id}/edit', name: 'app_highscore_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Highscore $highscore, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HighscoreType::class, $highscore);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_highscore_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('highscore/edit.html.twig', [
            'highscore' => $highscore,
            'form' => $form,
        ]);
    }

    #[IsGranted('access_admin')]
    #[Route('/{id}', name: 'app_highscore_delete', methods: ['POST'])]
    public function delete(Request $request, Highscore $highscore, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$highscore->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($highscore);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_highscore_index', [], Response::HTTP_SEE_OTHER);
    }
}
