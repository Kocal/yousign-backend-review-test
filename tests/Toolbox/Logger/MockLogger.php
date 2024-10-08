<?php
declare(strict_types=1);

namespace App\Tests\Toolbox\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

final class MockLogger extends AbstractLogger implements LoggerInterface
{
    public array $logs = [];

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }
    
    public function assertLogged(string $level, string $message, array|null $context = null): void
    {
        foreach ($this->logs as $log) {
            if ($log['level'] === $level && $log['message'] === $message && ($context === null || $log['context'] === $context)) {
                return;
            }
        }

        throw new \LogicException(sprintf('The log "%s" with level "%s" was not found.', $message, $level));
    }
}
