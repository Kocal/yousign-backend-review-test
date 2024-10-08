<?php
declare(strict_types=1);

namespace App\EventsFetcher;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fetches GitHub events from the GitHub Archive (https://www.gharchive.org/).
 */
final class GhArchiveEventsFetcher implements EventsFetcher
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/var/archives')]
        private readonly string $downloadDir,
        private readonly HttpClientInterface $ghArchiveClient,
        private readonly LoggerInterface|null $logger = new NullLogger(),
    ) {
    }

    public function fetchForDate(\DateTimeInterface $date): iterable
    {
        $archives = $this->downloadArchives(
            filenameFormat: sprintf('%s-%%d.json.gz', $date->format('Y-m-d'))
        );
        
        foreach ($archives as $archive) {
            yield from $this->readArchive($archive);
        }
        
        // TODO: maybe delete the downloaded archives after reading them?
    }

    /**
     * @param string $filenameFormat A valid "sprintf" format with a placeholder for the hour.
     * @return array<string> The paths to the downloaded archives.
     */
    private function downloadArchives(string $filenameFormat): array
    {
        $paths = [];
        $responses = [];

        // For one day, we have 24 archives (one per hour).
        for ($i = 0; $i < 24; $i++) {
            $filename = sprintf($filenameFormat, $i);
            $paths[] = $path = sprintf('%s/%s', $this->downloadDir, $filename);

            if (file_exists($path)) {
                $this->logger->debug('The archive "{path}" already exists, skipping downloading.', ['path' => $path]);
                continue;
            }
            
            $responses[] = $this->ghArchiveClient->request('GET', $filename, [
                'user_data' => [
                    'file' => fopen($path, 'wb') ?: throw new \RuntimeException(sprintf('Could not open the file "%s".', $path))
                ]
            ]);
        }

        foreach ($this->ghArchiveClient->stream($responses) as $response => $chunk) {
            if (!$chunk->isFirst() && !$chunk->isLast()) {
                if (!is_resource($file = $response->getInfo('user_data')['file'] ?? null)) {
                    throw new \LogicException('Missing "file" in "user_data" option.');
                }
                
                fwrite($file, $chunk->getContent());
            }
            
            if ($chunk->isLast()) {
                if (!is_resource($file = $response->getInfo('user_data')['file'] ?? null)) {
                    throw new \LogicException('Missing "file" in "user_data" option.');
                }
                
                fclose($file);
            }
        }

        return $paths;
    }

    private function readArchive(string $path): iterable
    {
        $handle = gzopen($path, 'rb') ?: throw new \RuntimeException(sprintf('Could not open the file "%s".', $path));

        try {
            while (!gzeof($handle)) {
                // TODO: Symfony 8.X: Maybe replace "json_encode" by https://github.com/symfony/symfony/pull/51718? 
                // and map decoded data to objects (more memory efficient and better DX)?
                yield json_decode(gzgets($handle), associative: true, flags: JSON_THROW_ON_ERROR);
            }
        } finally {
            gzclose($handle);
            unset($handle);
        }
    }
}
