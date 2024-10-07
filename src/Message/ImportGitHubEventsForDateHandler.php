<?php
declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ImportGitHubEventsForDateHandler
{
    public function __invoke(ImportGitHubEventsForDate $message): void
    {
        dump($message);
    }
}
