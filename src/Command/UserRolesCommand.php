<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:roles',
    description: 'Add or remove roles from user',
)]
class UserRolesCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email')
            ->addOption('add', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The roles to add')
            ->addOption('remove', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The roles to remove')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            throw new InvalidArgumentException(sprintf('User not found: %s', $email));
        }

        $roles = $user->getRoles();
        $roles = array_merge($roles, (array) $input->getOption('add'));
        $roles = array_diff($roles, (array) $input->getOption('remove'));
        $user->setRoles(array_unique($roles));
        $this->userRepository->save($user, true);

        $io->success(sprintf('User %s now has the roles %s', $user->getEmail(), implode(', ', $user->getRoles())));

        return Command::SUCCESS;
    }
}
