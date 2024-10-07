<?php
declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\ImportGitHubEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

final class ImportGitHubEventsTest extends TestCase
{
    private ClockInterface $clock;

    protected function setUp(): void
    {
        $this->clock = Clock::get();
    }
    
    protected function tearDown(): void
    {
        Clock::set($this->clock);
    }

    private static function provideValidationOnCreation(): iterable 
    {
        yield 'no dates nor relative period' => [
            new \InvalidArgumentException('You must provide a start date, an end date or a relative period.'),
            [
                'startDate' => null,
                'endDate' => null,
                'relativePeriod' => null,
            ],
        ];
        
        yield 'dates and relative period provided' => [
            new \InvalidArgumentException('You cannot provide both dates and a relative period.'),
            [
                'startDate' => new \DateTimeImmutable('2021-01-01'),
                'endDate' => new \DateTimeImmutable('2021-01-03'),
                'relativePeriod' => '-1 month',
            ],
        ];
        
        yield 'start date after end date' => [
            new \InvalidArgumentException('The start date must be before the end date.'),
            [
                'startDate' => new \DateTimeImmutable('2021-01-01'),
                'endDate' => new \DateTimeImmutable('2020-01-01'),
                'relativePeriod' => null,
            ],
        ];
        
        yield 'start date and end date less than one day apart' => [
            new \InvalidArgumentException('The start date and the end date must be at least one day apart.'),
            [
                'startDate' => new \DateTimeImmutable('2021-01-01'),
                'endDate' => new \DateTimeImmutable('2021-01-01'),
                'relativePeriod' => null,
            ],
        ];
        
        yield 'invalid relative period' => [
            new \InvalidArgumentException('The relative period "foobar" is invalid.'),
            [
                'startDate' => null,
                'endDate' => null,
                'relativePeriod' => 'foobar',
            ],
        ];
    }
    
    /**
     * @dataProvider provideValidationOnCreation
     */
    public function testValidationOnCreation(\Exception $expectedException, array $constructorArgs): void
    {
        $this->expectExceptionObject($expectedException);
        
        new ImportGitHubEvents(...$constructorArgs);
    }
    
    public function provideGetDatePeriod(): iterable
    {
        Clock::set(new MockClock(new \DateTimeImmutable('2021-01-04')));
        
        yield '3 days period, with start and end dates' => [
            [
                new \DateTimeImmutable('2021-01-01'),
                new \DateTimeImmutable('2021-01-02'),
                new \DateTimeImmutable('2021-01-03'),
            ],
            new ImportGitHubEvents(
                new \DateTimeImmutable('2021-01-01'),
                new \DateTimeImmutable('2021-01-03'),
                null,
            ),
        ];
        
        yield '3 days period, with relative period' => [
            [
                new \DateTimeImmutable('2021-01-01'),
                new \DateTimeImmutable('2021-01-02'),
                new \DateTimeImmutable('2021-01-03'),
            ],
            new ImportGitHubEvents(
                null,
                null,
                '-3 days',
            ),
        ];
    }

    /**
     * @dataProvider provideGetDatePeriod
     */
    public function testGetDatePeriod(array $expectedDateTimes, ImportGitHubEvents $sut): void
    {
        $datePeriod = $sut->getDatePeriod();
        $dateTimes = [...$datePeriod];

        self::assertCount(count($expectedDateTimes), $dateTimes);
        self::assertEquals($expectedDateTimes, $dateTimes);
    }
}
