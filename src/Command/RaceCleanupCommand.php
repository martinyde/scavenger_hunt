<?php

namespace App\Command;

use App\Repository\RaceRepository;
use App\Repository\UserRepository;
use App\Service\GenericHelper;
use App\Service\RaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'app:race:cleanup',
  description: 'Remove races that are finished.',
)]
class RaceCleanupCommand extends Command
{
  public function __construct(
    private readonly RaceHelper $raceHelper,
    private readonly GenericHelper $genericHelper,
    protected EntityManagerInterface $entityManager,
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $finishedRaces = $this->raceHelper->getFinishedRaces();
    $this->genericHelper->removeParticipantSessionById(326);
    foreach ($finishedRaces as $race) {
      foreach ($race->getParticipants() as $participant) {

        $this->entityManager->remove($participant);
      }
      $this->entityManager->remove($race);
    }
    $this->entityManager->flush();

    return Command::SUCCESS;
  }
}
