<?php
declare(strict_types=1);

namespace App\Message;

final class ImportGitHubEvents
{
    public function __construct(
        private readonly \DateTimeInterface|null $startDate,
        private readonly \DateTimeInterface|null $endDate,
        private readonly string|null $relativePeriod,
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
}
