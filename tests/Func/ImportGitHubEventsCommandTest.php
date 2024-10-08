<?php
declare(strict_types=1);

namespace App\Tests\Func;

use App\Message\ImportGitHubEvents;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class ImportGitHubEventsCommandTest extends KernelTestCase
{
    private Application $application;
    private InMemoryTransport $transportSync;
    private InMemoryTransport $transportAsync;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->application = new Application($kernel);

        $this->transportSync = self::getContainer()->get('messenger.transport.sync');
        $this->transportSync->reset();

        $this->transportAsync = self::getContainer()->get('messenger.transport.async');
        $this->transportAsync->reset();
    }

    public function testErrorShouldDisplayIfNoArgumentsArePassed(): void
    {
        self::expectExceptionObject(new \InvalidArgumentException('You must use the "start-date" and "end-date" arguments together, or use "relative-period" argument alone.'));

        $command = $this->application->find('app:import-github-events');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        self::assertEmpty($this->transportSync->getSent());
        self::assertEmpty($this->transportAsync->getSent());
    }

    public function testErrorShouldDisplayIfOnlyStartDateArgumentIsPassed(): void
    {
        self::expectExceptionObject(new \InvalidArgumentException('You must use the "start-date" and "end-date" arguments together.'));

        $command = $this->application->find('app:import-github-events');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--start-date' => '2024-01-01',
        ]);

        self::assertEmpty($this->transportSync->getSent());
        self::assertEmpty($this->transportAsync->getSent());
    }

    public function testErrorShouldDisplayIfEndDateIsBeforeStartDate(): void
    {
        self::expectExceptionObject(new \InvalidArgumentException('The start date must be before the end date.'));

        $command = $this->application->find('app:import-github-events');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--start-date' => '2024-01-01',
            '--end-date' => '2023-12-25',
        ]);

        self::assertEmpty($this->transportSync->getSent());
        self::assertEmpty($this->transportAsync->getSent());
    }

    /**
     * @testWith [true]
     * @testWith [false]
     */
    public function testMessageShouldBeDispatchedWhenPassingTwoDates(bool $async): void
    {
        $command = $this->application->find('app:import-github-events');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--start-date' => '2024-01-01',
            '--end-date' => '2024-11-08',
            ...($async ? ['--async' => null] : []),
        ]);

        $expectedTransportWithEnvelopes = $async ? $this->transportAsync : $this->transportSync;
        $expectedTransportWithoutEnvelopes = $async ? $this->transportSync : $this->transportAsync;
        
        self::assertCount(1, $expectedTransportWithEnvelopes->getSent());
        self::assertEmpty($expectedTransportWithoutEnvelopes->getSent());

        $envelope = $expectedTransportWithEnvelopes->getSent()[0];
        self::assertInstanceOf(Envelope::class, $envelope);
        self::assertEquals(new TransportNamesStamp([$async ? 'async' : 'sync']), $envelope->all(TransportNamesStamp::class)[0]);
        self::assertEquals(new ImportGitHubEvents(
            startDate: new \DateTimeImmutable('2024-01-01'),
            endDate: new \DateTimeImmutable('2024-11-08'),
            relativePeriod: null,
        ), $envelope->getMessage());
    }

    /**
     * @testWith [true]
     * @testWith [false]
     */
    public function testMessageShouldBeDispatchedWhenPassingRelativePeriod(bool $async): void
    {
        $command = $this->application->find('app:import-github-events');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--relative-period' => '-1 week',
            ...($async ? ['--async' => null] : []),
        ]);

        $expectedTransportWithEnvelopes = $async ? $this->transportAsync : $this->transportSync;
        $expectedTransportWithoutEnvelopes = $async ? $this->transportSync : $this->transportAsync;

        self::assertCount(1, $expectedTransportWithEnvelopes->getSent());
        self::assertEmpty($expectedTransportWithoutEnvelopes->getSent());

        $envelope = $expectedTransportWithEnvelopes->getSent()[0];
        self::assertInstanceOf(Envelope::class, $envelope);
        self::assertEquals(new TransportNamesStamp([$async ? 'async' : 'sync']), $envelope->all(TransportNamesStamp::class)[0]);
        self::assertEquals(new ImportGitHubEvents(
            startDate: null,
            endDate: null,
            relativePeriod: '-1 week',
        ), $envelope->getMessage());
    }
}
