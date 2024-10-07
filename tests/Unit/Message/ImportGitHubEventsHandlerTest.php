<?php
declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\ImportGitHubEvents;
use App\Message\ImportGitHubEventsForDate;
use App\Message\ImportGitHubEventsHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class ImportGitHubEventsHandlerTest extends TestCase
{
    public function testInvokeShouldDispatchMessages(): void
    {
        $sut = new ImportGitHubEventsHandler(
            $messageBus = new class implements MessageBusInterface
            {
                public array $messages = [];
                
                public function dispatch(object $message, array $stamps = []): Envelope
                {
                    $this->messages[] = $message;
                    return new Envelope($message, $stamps);
                }
            }
        );
        
        $sut(new ImportGitHubEvents(
            startDate: new \DateTimeImmutable('2021-01-01'),
            endDate: new \DateTimeImmutable('2021-01-03')
        ));
        
        self::assertCount(3, $messageBus->messages);
        self::assertEquals(new ImportGitHubEventsForDate(
            date: new \DateTimeImmutable('2021-01-01')
        ), $messageBus->messages[0]);
        self::assertEquals(new ImportGitHubEventsForDate(
            date: new \DateTimeImmutable('2021-01-02')
        ), $messageBus->messages[1]);
        self::assertEquals(new ImportGitHubEventsForDate(
            date: new \DateTimeImmutable('2021-01-03')
        ), $messageBus->messages[2]);
    }
}
