<?php

namespace App\Controller;

use App\Entity\ScavengerHunt;
use App\Entity\User;
use App\Form\ScavengerHuntType;
use App\Repository\ScavengerHuntRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ScavengerHuntController extends AbstractController
{
    #[Route('/scavenger/hunt', name: 'app_scavenger_hunt_index', methods: ['GET'])]
    #[IsGranted('view')]
    public function index(ScavengerHuntRepository $scavengerHuntRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->render('scavenger_hunt/index.html.twig', [
                'scavenger_hunts' => $scavengerHuntRepository->findAll()]
            );
        }

        return $this->render('scavenger_hunt/index.html.twig', [
            'scavenger_hunts' => $scavengerHuntRepository->findBy(['user' => $user->getId()]),
        ]);
    }

    #[Route('/scavenger/hunt/new', name: 'app_scavenger_hunt_new', methods: ['GET', 'POST'])]
    #[IsGranted('view')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $scavengerHunt = new ScavengerHunt();
        $form = $this->createForm(ScavengerHuntType::class, $scavengerHunt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $scavengerHunt->setUser($user);
            $entityManager->persist($scavengerHunt);
            $entityManager->flush();

            return $this->redirectToRoute('app_scavenger_hunt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('scavenger_hunt/new.html.twig', [
            'scavenger_hunt' => $scavengerHunt,
            'form' => $form,
        ]);
    }

    #[Route('/scavenger/hunt/{id}', name: 'app_scavenger_hunt_show', methods: ['GET'])]
    #[IsGranted('view', subject: 'scavengerHunt')]
    public function show(ScavengerHunt $scavengerHunt, int $id): Response
    {
        return $this->render('scavenger_hunt/show.html.twig', [
            'scavenger_hunt' => $scavengerHunt,
        ]);
    }

    #[Route('/scavenger/hunt/{id}/edit', name: 'app_scavenger_hunt_edit', methods: ['GET', 'POST'])]
    #[IsGranted('view', subject: 'scavengerHunt')]
    public function edit(Request $request, ScavengerHunt $scavengerHunt, EntityManagerInterface $entityManager, int $id): Response
    {
        $form = $this->createForm(ScavengerHuntType::class, $scavengerHunt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_scavenger_hunt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('scavenger_hunt/edit.html.twig', [
            'scavenger_hunt' => $scavengerHunt,
            'form' => $form,
        ]);
    }

    #[Route('/scavenger/hunt/{id}', name: 'app_scavenger_hunt_delete', methods: ['POST'])]
    #[IsGranted('view', subject: 'scavengerHunt')]
    public function delete(Request $request, ScavengerHunt $scavengerHunt, EntityManagerInterface $entityManager, int $id): Response
    {
        if ($this->isCsrfTokenValid('delete'.$scavengerHunt->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($scavengerHunt);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_scavenger_hunt_index', [], Response::HTTP_SEE_OTHER);
    }
}
