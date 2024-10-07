<?php
declare(strict_types=1);

namespace App\Message;

use function Symfony\Component\Clock\now;

final class ImportGitHubEvents
{
    public function __construct(
        private readonly \DateTimeInterface|null $startDate = null,
        private readonly \DateTimeInterface|null $endDate = null,
        private readonly string|null $relativePeriod = null,
    ) {
        if ($this->startDate === null && $this->endDate === null && $this->relativePeriod === null) {
            throw new \InvalidArgumentException('You must provide a start date, an end date or a relative period.');
        } elseif ($this->startDate !== null && $this->endDate !== null && $this->relativePeriod !== null) {
            throw new \InvalidArgumentException('You cannot provide both dates and a relative period.');
        }

        if ($this->startDate !== null && $this->endDate !== null) {
            $diff = $this->startDate->diff($this->endDate);
            if ($diff->invert === 1) {
                throw new \InvalidArgumentException('The start date must be before the end date.');
            }
            if ($diff->days < 1) {
                throw new \InvalidArgumentException('The start date and the end date must be at least one day apart.');
            }
        }
        
        if ($this->relativePeriod !== null) {
           try {
               new \DateTimeImmutable($this->relativePeriod);
           } catch (\Throwable $e) {
               throw new \InvalidArgumentException(sprintf('The relative period "%s" is invalid.', $this->relativePeriod), previous: $e);
           }
        }
    }
    
    public function getDatePeriod(): \DatePeriod
    {
        if ($this->startDate && $this->endDate) {
            $startDate = $this->startDate;
            $endDate = \DateTimeImmutable::createFromInterface($this->endDate)
                // Include the end date in the period // TODO: PHP <8.2: to remove when upgrading to PHP 8.2
                ->modify('+1 day')
            ;
        } else if ($this->relativePeriod) {
            $endDate = now();
            $startDate = $endDate->modify($this->relativePeriod);
        }
        
        return new \DatePeriod(
            $startDate, 
            new \DateInterval('P1D'), 
            $endDate,
            // $this->relativePeriod ? 0 : \DatePeriod::INCLUDE_END_DATE // TODO: PHP 8.2: uncomment when upgrading to PHP 8.2
        );
    }
}
