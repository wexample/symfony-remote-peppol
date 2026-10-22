<?php

namespace Wexample\SymfonyRemotePeppol\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wexample\SymfonyRemotePeppol\Service\InboxCollector;

/**
 * For access points without webhooks, or to catch up: run it on a schedule.
 */
#[AsCommand(name: 'peppol:collect', description: 'Hand every document waiting in the Peppol inboxes to the application.')]
class PeppolCollectCommand extends Command
{
    public function __construct(
        private readonly InboxCollector $collector,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $output->writeln(sprintf('%d document(s) received.', $this->collector->collect()));

        return Command::SUCCESS;
    }
}
