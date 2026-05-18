<?php

namespace App\MessageHandler;

use App\Message\RaceEndMessage;
use App\Repository\RaceRepository;
use App\Service\GenericHelper;
use App\Service\RaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RaceEndMessageHandler
{
    public function __construct(
        private RaceRepository $raceRepository,
        private EntityManagerInterface $entityManager,
        private GenericHelper $genericHelper,
        private RaceHelper $raceHelper,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function __invoke(RaceEndMessage $message): void
    {
        $races = $this->raceRepository->findBy(['active' => true]);
        $now = new \DateTimeImmutable();

        foreach ($races as $race) {
            if ($race->getTimer()->modify('+'.$race->getRaceDuration().' seconds') < $now) {
                $race->setActive(false);
                $this->entityManager->flush();
                $this->genericHelper->createHighscores($race);

                $this->raceHelper->publishRaceStateChanged($race);
            }
        }
    }
}
