<?php
declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\ImportGitHubEvents;
use PHPUnit\Framework\TestCase;

final class ImportGitHubEventsTest extends TestCase
{
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
}
