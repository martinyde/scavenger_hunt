<?php

declare(strict_types=1);

namespace App\Controller;

use Scavenger\Shared\ApiClient\AdminApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HuntController extends AbstractController
{
    public function __construct(
        private readonly AdminApiClient $adminApiClient,
    ) {
    }

    #[Route('/hunts', name: 'app_hunts', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $hunts = $this->adminApiClient->getScavengerHunts();

        if ($this->wantsJson($request)) {
            return $this->json(array_map(fn ($h) => [
                'id' => $h->id,
                'name' => $h->name,
                'task_count' => count($h->tasks),
                'race_count' => count($h->races),
            ], $hunts));
        }

        return $this->render('hunt/index.html.twig', [
            'hunts' => $hunts,
        ]);
    }

    #[Route('/hunts/{id}', name: 'app_hunt_show', methods: ['GET'])]
    public function show(int $id, Request $request): Response
    {
        $hunt = $this->adminApiClient->getScavengerHunt($id);

        if ($this->wantsJson($request)) {
            return $this->json([
                'id' => $hunt->id,
                'name' => $hunt->name,
                'tasks' => array_map(fn ($t) => [
                    'id' => $t->id,
                    'uuid' => $t->uuid,
                    'title' => $t->title,
                ], $hunt->tasks),
                'races' => array_map(fn ($r) => [
                    'id' => $r->id,
                    'uuid' => $r->uuid,
                    'active' => $r->active,
                    'type' => $r->type,
                ], $hunt->races),
            ]);
        }

        return $this->render('hunt/show.html.twig', [
            'hunt' => $hunt,
        ]);
    }

    #[Route('/api/hunts', name: 'api_hunts', methods: ['GET'])]
    public function apiIndex(): JsonResponse
    {
        $hunts = $this->adminApiClient->getScavengerHunts();

        return $this->json(array_map(fn ($h) => [
            'id' => $h->id,
            'name' => $h->name,
            'task_count' => count($h->tasks),
            'race_count' => count($h->races),
        ], $hunts));
    }

    #[Route('/api/hunts/{id}', name: 'api_hunt_show', methods: ['GET'])]
    public function apiShow(int $id): JsonResponse
    {
        $hunt = $this->adminApiClient->getScavengerHunt($id);

        return $this->json([
            'id' => $hunt->id,
            'name' => $hunt->name,
            'tasks' => array_map(fn ($t) => [
                'id' => $t->id,
                'uuid' => $t->uuid,
                'title' => $t->title,
            ], $hunt->tasks),
            'races' => array_map(fn ($r) => [
                'id' => $r->id,
                'uuid' => $r->uuid,
                'active' => $r->active,
                'type' => $r->type,
            ], $hunt->races),
        ]);
    }

    private function wantsJson(Request $request): bool
    {
        return str_contains($request->headers->get('Accept', ''), 'application/json');
    }
}
