<?php
declare(strict_types=1);

namespace App\Message;

use App\EventsFetcher\EventsFetcher;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ImportGitHubEventsForDateHandler
{
    public function __construct(
        private readonly EventsFetcher   $eventsFetcher,
        private readonly LoggerInterface|null $logger = new NullLogger(),
    ) {
    }

    public function __invoke(ImportGitHubEventsForDate $message): void
    {
        $this->logger->info('Handling import of GitHub events for {date}', ['date' => $message->getDate()]);
        
        foreach($this->eventsFetcher->fetchForDate($message->getDate()) as $eventPayload) {
            dd($eventPayload);
        }
    }
}
