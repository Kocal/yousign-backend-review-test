<?php
declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class ImportGitHubEventsHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(ImportGitHubEvents $message)
    {
        foreach ($message->getDatePeriod() as $date) {
            $this->messageBus->dispatch(new ImportGitHubEventsForDate($date));
        }
    }
}
