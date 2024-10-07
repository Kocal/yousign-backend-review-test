<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\ImportGitHubEvents;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use function Symfony\Component\Clock\now;

/**
 * This command must import GitHub events.
 * You can add the parameters and code you want in this command to meet the need.
 */
#[AsCommand(
    name: 'app:import-github-events',
    description: 'Import GH events',
)]
class ImportGitHubEventsCommand extends Command
{
    private SymfonyStyle $io;
    private bool $isAsync;
    private \DateTimeInterface|null $startDate;
    private \DateTimeInterface|null $endDate;
    private string|null $relativePeriod;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import GH events');
        $this
            ->addOption('async', null, InputOption::VALUE_NONE, 'Execute the command asynchronously.')
            ->addOption('start-date', null, InputOption::VALUE_OPTIONAL, 'A valid DateTime format (ex: "2024-01-01").', null)
            ->addOption('end-date', null, InputOption::VALUE_OPTIONAL, 'A valid DateTime format (ex: "2024-11-08").', null)
            ->addOption('relative-period', null, InputOption::VALUE_OPTIONAL, 'A valid relative period (ex: "-3 weeks", "-2 months").', null)
            ->addUsage('--start-date="2024-01-01" --end-date="2024-11-08"')
            ->addUsage('--start-date="2024-01-01" --end-date="2024-11-08" --async')
            ->addUsage('--relative-period="-3 weeks"')
            ->addUsage('--relative-period="-3 weeks" --async')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->isAsync = $input->getOption('async');
        
        $startDate = $input->getOption('start-date');
        $endDate = $input->getOption('end-date');
        $relativePeriod = $input->getOption('relative-period');
        
        if (!$startDate && !$endDate && !$relativePeriod) {
            throw new \InvalidArgumentException('You must use the "start-date" "end-date" or "relative-period" arguments.');
        }
        if ($relativePeriod && ($startDate || $endDate)) {
            throw new \InvalidArgumentException('You cannot use the "relative-period" argument with "start-date" or "end-date" arguments.');
        }
        
        $this->startDate = $startDate ? now($startDate) : null;
        $this->endDate = $startDate ? now($endDate) : null;
        $this->relativePeriod = $relativePeriod;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = new ImportGitHubEvents(
            startDate: $this->startDate,
            endDate: $this->endDate,
            relativePeriod: $this->relativePeriod,
        );

        if ($this->isAsync) {
            $this->io->success('The import will start asynchronously.');
            $this->messageBus->dispatch($message, [new TransportNamesStamp(['async'])]);
        } else {
            $this->io->info('The import will start now.');
            $this->messageBus->dispatch($message, [new TransportNamesStamp(['sync'])]);
        }

        return self::SUCCESS;
    }
}
