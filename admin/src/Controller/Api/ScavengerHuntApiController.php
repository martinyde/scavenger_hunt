<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\ScavengerHunt;
use App\Repository\ScavengerHuntRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Scavenger Hunts')]
final class ScavengerHuntApiController extends AbstractController
{
    #[Route('/api/v1/scavenger-hunts', name: 'api_scavenger_hunt_list', methods: ['GET'])]
    #[OA\Get(summary: 'List all scavenger hunts')]
    #[OA\Response(
        response: 200,
        description: 'List of scavenger hunts',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'City Explorer'),
                new OA\Property(property: 'task_count', type: 'integer', example: 5),
                new OA\Property(property: 'race_count', type: 'integer', example: 3),
            ]),
        ),
    )]
    public function list(ScavengerHuntRepository $repository): JsonResponse
    {
        $hunts = $repository->findAll();

        return $this->json(array_map(fn (ScavengerHunt $h) => [
            'id' => $h->getId(),
            'name' => $h->getName(),
            'task_count' => $h->getTasks()->count(),
            'race_count' => $h->getRaces()->count(),
        ], $hunts));
    }

    #[Route('/api/v1/scavenger-hunts/{id}', name: 'api_scavenger_hunt_show', methods: ['GET'])]
    #[OA\Get(summary: 'Get hunt details with tasks and races')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Hunt details',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(
                property: 'tasks',
                type: 'array',
                items: new OA\Items(properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'passKey', type: 'string'),
                    new OA\Property(property: 'textBefore', type: 'string', nullable: true),
                    new OA\Property(property: 'textAfter', type: 'string', nullable: true),
                    new OA\Property(property: 'solutions', type: 'array', items: new OA\Items(type: 'string')),
                ]),
            ),
            new OA\Property(
                property: 'races',
                type: 'array',
                items: new OA\Items(properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'active', type: 'boolean'),
                    new OA\Property(property: 'type', type: 'string'),
                ]),
            ),
        ]),
    )]
    public function show(ScavengerHunt $scavengerHunt): JsonResponse
    {
        return $this->json([
            'id' => $scavengerHunt->getId(),
            'name' => $scavengerHunt->getName(),
            'tasks' => array_map(fn ($t) => [
                'id' => $t->getId(),
                'uuid' => $t->getUuid()->toString(),
                'title' => $t->getTitle(),
                'passKey' => $t->getPassKey(),
                'textBefore' => $t->getTextBefore(),
                'textAfter' => $t->getTextAfter(),
                'solutions' => $t->getSolutions(),
            ], $scavengerHunt->getTasks()->toArray()),
            'races' => array_map(fn ($r) => [
                'id' => $r->getId(),
                'uuid' => $r->getUuid()->toString(),
                'active' => $r->isActive(),
                'type' => $r->getType(),
            ], $scavengerHunt->getRaces()->toArray()),
        ]);
    }
}
