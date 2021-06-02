<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';

    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var EntityManagerInterface
     */
    private $manager;


    public function __construct(UserRepository $repository, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        parent::__construct();

        $this->repository = $repository;
        $this->encoder = $encoder;
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email address')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Add admin role');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $admin = $input->getOption('admin');

        $user = $this->repository->findOneBy([
            'email' => $email,
        ]);

        if (null !== $user) {
            $output->writeln('<error>User already exists</error>');

            return Command::SUCCESS;
        }

        $user = new User();
        $user->setEmail($email);

        if ($admin) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $encoded = $this->encoder->encodePassword($user, $password);
        $user->setPassword($encoded);

        $this->manager->persist($user);
        $this->manager->flush();

        return Command::SUCCESS;
    }

}
