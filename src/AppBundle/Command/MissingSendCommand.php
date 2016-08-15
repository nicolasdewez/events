<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MissingSendCommand.
 */
class MissingSendCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:message:missing_send')
            ->setDescription('Missing send messages')
            ->addOption('messages', 'm', InputOption::VALUE_REQUIRED + InputOption::VALUE_IS_ARRAY, 'Message ids', [])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get('app.manager.message');

        // Load messages
        if ($input->getOption('messages')) {
            $messages = $manager->loadById($input->getOption('messages'));
        } else {
            $messages = $manager->loadByStateToMissingSend();
        }

        // Process
        $this->getContainer()->get('app.event.publish')->resendMissingMessages($messages);
    }
}
