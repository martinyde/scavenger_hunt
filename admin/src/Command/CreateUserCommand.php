<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create a user',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'The password of the user, if not set, a random password will be generated')
            ->addOption('roles', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Roles to add to the user', ['ROLE_ADMIN'])
        ;
    }

    /**
     * @throws RandomException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $password = $input->getOption('password');
        $roles = $input->getOption('roles');

        if (!$password) {
            $password = bin2hex(random_bytes(5));
            $io->note('Password: '.$password);
        }

        $user = new User();

        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(array_unique($roles));
        $user->setIsVerified(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User have been created.');

        return Command::SUCCESS;
    }
}
