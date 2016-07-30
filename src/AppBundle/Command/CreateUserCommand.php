<?php

namespace AppBundle\Command;

use AppBundle\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateUserCommand.
 */
class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:user:create')
            ->setDescription('Create user')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::REQUIRED, 'Password (not encoded)')
            ->addArgument('firstName', InputArgument::OPTIONAL, 'First name')
            ->addArgument('lastName', InputArgument::OPTIONAL, 'Last name')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $user = new Users();
        $user
            ->setUsername($input->getArgument('username'))
            ->setPassword($input->getArgument('password'))
            ->setFirstName($input->getArgument('firstName') ?: '')
            ->setLastName($input->getArgument('lastName') ?: '')
        ;

        $user->setPassword(
            $this->getContainer()->get('security.password_encoder')->encodePassword($user, $user->getPassword())
        );

        $errors = $this->getContainer()->get('validator')->validate($user);
        if (count($errors)) {
            $output->writeln('<error>User is not valid.</error>');
            $output->writeln((string) $errors);

            return;
        }

        $em->persist($user);
        $em->flush();

        $output->writeln('<info>User created.</info>');
    }
}
