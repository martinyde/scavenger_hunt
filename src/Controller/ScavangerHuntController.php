<?php

namespace App\Controller;

use App\Entity\ScavangerHunt;
use App\Form\ScavangerHuntType;
use App\Repository\ScavangerHuntRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/scavanger/hunt')]
final class ScavangerHuntController extends AbstractController
{
    #[Route(name: 'app_scavanger_hunt_index', methods: ['GET'])]
    public function index(ScavangerHuntRepository $scavangerHuntRepository): Response
    {
        return $this->render('scavanger_hunt/index.html.twig', [
            'scavanger_hunts' => $scavangerHuntRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_scavanger_hunt_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $scavangerHunt = new ScavangerHunt();
        $form = $this->createForm(ScavangerHuntType::class, $scavangerHunt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($scavangerHunt);
            $entityManager->flush();

            return $this->redirectToRoute('app_scavanger_hunt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('scavanger_hunt/new.html.twig', [
            'scavanger_hunt' => $scavangerHunt,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_scavanger_hunt_show', methods: ['GET'])]
    public function show(ScavangerHunt $scavangerHunt): Response
    {
        return $this->render('scavanger_hunt/show.html.twig', [
            'scavanger_hunt' => $scavangerHunt,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_scavanger_hunt_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ScavangerHunt $scavangerHunt, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ScavangerHuntType::class, $scavangerHunt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_scavanger_hunt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('scavanger_hunt/edit.html.twig', [
            'scavanger_hunt' => $scavangerHunt,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_scavanger_hunt_delete', methods: ['POST'])]
    public function delete(Request $request, ScavangerHunt $scavangerHunt, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$scavangerHunt->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($scavangerHunt);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_scavanger_hunt_index', [], Response::HTTP_SEE_OTHER);
    }
}
