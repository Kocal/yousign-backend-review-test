<?php
declare(strict_types=1);

namespace App\Tests\Unit\EventsFetcher;

use App\EventsFetcher\GhArchiveEventsFetcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class GhArchiveEventsFetcherTest extends TestCase
{
    private string $downloadDir;

    protected function setUp(): void
    {
        $this->downloadDir = sys_get_temp_dir().'/yousign-backend-review-test/gh-archive';        
        
        $this->fs = new Filesystem();
        $this->fs->remove($this->downloadDir);
        $this->fs->mkdir($this->downloadDir);
    }
    
    protected function tearDown(): void
    {
        $this->fs->remove($this->downloadDir);
    }

    public function testFetchForDateShouldYieldGitHubEvents(): void
    {
        $sut = new GhArchiveEventsFetcher(
            $this->downloadDir,
            new MockHttpClient(
                function (string $method, string $url) {
                    if (preg_match('/^https:\/\/data\.gharchive\.org\/2024-10-07-\d+\.json\.gz$/', $url)) {
                        $filename = basename($url);
                        return new MockResponse(
                            file_get_contents(__DIR__ . '/../../fixtures/gh-archives/' . $filename), [
                            'response_headers' => ['Content-Type' => 'application/gzip'],
                        ]);
                    }
                    
                    throw new \RuntimeException(sprintf('Unexpected URL "%s".', $url));
                },
                baseUri: 'https://data.gharchive.org',
            )
        );
     
        $events = [];
        foreach ($sut->fetchForDate(new \DateTime('2024-10-07')) as $event) {
            $events[] = $event;
        }
        
        self::assertCount(2 * 24, $events); // See tests/fixtures/gh-archives/README.md, we have 2 events per hour.
        self::assertSame("42587052138", $events[0]['id']);
        self::assertSame("42588575915", $events[5]['id']);
        self::assertSame("42591298698", $events[10]['id']);
        self::assertSame("42593976205", $events[15]['id']);
        self::assertSame("42600246651", $events[20]['id']);
        self::assertSame("42602365544", $events[23]['id']);
    }
}
