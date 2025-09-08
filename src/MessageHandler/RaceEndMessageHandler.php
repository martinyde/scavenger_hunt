<?php

namespace App\MessageHandler;

use App\Message\RaceEndMessage;
use App\Repository\RaceRepository;
use App\Service\GenericHelper;
use App\Service\RaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Twig\Environment;

#[AsMessageHandler]
final readonly class RaceEndMessageHandler
{
    public function __construct(
        protected RaceRepository $raceRepository,
        protected EntityManagerInterface $entityManager,
        protected HubInterface $hub,
        protected Environment $twig,
        protected GenericHelper $genericHelper,
        protected RaceHelper $raceHelper,
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
                $race = $this->raceHelper->finishRace($race);

                $update = new Update(
                    'race_state_changed',
                    $this->twig->render('broadcast/RaceParticipant.stream.html.twig', [
                        'race' => $race,
                        'tryform' => null,
                    ])
                );

                $this->hub->publish($update);

                $this->raceHelper->removeParticipants($race);


            }
        }
    }
}
