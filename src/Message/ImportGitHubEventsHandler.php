<?php
declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ImportGitHubEventsHandler
{
    public function __invoke(ImportGitHubEvents $message)
    {
        dump($message);
    }
}
