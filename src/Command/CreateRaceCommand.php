<?php

namespace App\Command;

use App\Entity\Race;
use App\Entity\ScavengerHunt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
  name: 'app:race:create',
  description: 'Create a new race from a Svanger hunt',
)]
class CreateRaceCommand extends Command
{
  public function __construct(
    private readonly EntityManagerInterface $entityManager,
    private readonly UrlGeneratorInterface $urlGenerator,
  ) {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this
      ->addArgument('scavenger_hunt', InputArgument::REQUIRED, 'The id of the scavenger hunt to create the race from.')
      ->addOption('duration', null, InputOption::VALUE_OPTIONAL, 'The duration of the race in seconds. Default: 300', 300)
      ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type of the race single/repeating. Default: "single"', 'single')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);

    $scavengerHuntId = $input->getArgument('scavenger_hunt');
    $duration = $input->getOption('duration');
    $type = $input->getOption('type');

    $scavengerHunt = $this->entityManager->getRepository(ScavengerHunt::class)->find($scavengerHuntId);
    $race = new Race();

    $race->setScavengerHunt($scavengerHunt);
    $race->setRaceDuration($duration);
    $race->setType($type);
    $race->setActive(false);
    $this->entityManager->persist($race);
    $this->entityManager->flush();

    $context = $this->urlGenerator->getContext();
    // You can modify it if needed
    $context->setHost('/');
    $context->setScheme('https');


    $io->success('Race has been created.');
    $io->success('Panel: ' . $this->urlGenerator->generate('app_race_progress', ['id' => $race->getId()], UrlGeneratorInterface::ABSOLUTE_URL));
    $io->success('Participant: ' . $this->urlGenerator->generate('app_race_show', ['id' => $race->getId(), 'uuid' => $race->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL));

    return Command::SUCCESS;
  }
}