<?php

namespace App\MessageHandler;

use App\Message\RaceEndMessage;
use App\Repository\RaceRepository;
use App\Service\GenericHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Twig\Environment;

#[AsMessageHandler]
final readonly class RaceEndMessageHandler
{
    public function __construct(
        private RaceRepository $raceRepository,
        private EntityManagerInterface $entityManager,
        private HubInterface $hub,
        private Environment $twig,
        private GenericHelper $genericHelper,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
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

                $update = new Update(
                    'race_state_changed',
                    $this->twig->render('broadcast/RaceParticipant.stream.html.twig', [
                        'race' => $race,
                        'tryform' => null,
                    ])
                );

                $this->hub->publish($update);
            }
        }
    }
}
