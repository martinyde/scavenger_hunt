<?php

namespace App\Command;

use App\Service\RaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
  name: 'app:race:cleanup',
  description: 'Remove races that are finished.',
)]
class RaceCleanupCommand extends Command
{
  public function __construct(
    private readonly RaceHelper $raceHelper,
    protected EntityManagerInterface $entityManager,
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->raceHelper->removeFinishedRaces();

    return Command::SUCCESS;
  }
}
