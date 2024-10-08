<?php
declare(strict_types=1);

namespace App\Tests\Unit\Normalizer;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use App\Normalizer\ActorNormalizer;
use App\Normalizer\EventNormalizer;
use App\Normalizer\RepoNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

final class EventNormalizerTest extends TestCase
{
    private static function provideSupportsDenormalization(): iterable
    {
        yield [true, [], Event::class, EventNormalizer::FORMAT_GITHUB_JSON_PAYLOAD];
        yield [false, [], Event::class, 'json'];
        yield [false, [], 'App\\Entity\\Repository', EventNormalizer::FORMAT_GITHUB_JSON_PAYLOAD];
    }

    /**
     * @dataProvider provideSupportsDenormalization
     */
    public function testSupportsDenormalization(bool $expected, mixed $data, string $type, string $format = null): void
    {
        $sut = new EventNormalizer();

        self::assertSame($expected, $sut->supportsDenormalization($data, $type, $format));
    }

    public function testDenormalization(): void
    {
        $normalizedData = [
            'id' => 4,
            'type' => 'PullRequestEvent',
            'actor' => [
                'id' => 1,
                'login' => 'Kocal',
                'url' => 'https://github.com/Kocal',
                'avatar_url' => 'https://avatars.githubusercontent.com/u/1',
            ],
            'repo' => [
                'id' => 1,
                'name' => 'Kocal/BiomeJsBundle',
                'url' => 'https://github.com/Kocal/BiomeJsBundle',
            ],
            'payload' => [
                'action' => 'opened',
            ],
            'created_at' => '2021-08-01T00:00:00Z',
        ];

        $sut = new EventNormalizer();
        $sut->setDenormalizer(new Serializer([new ActorNormalizer(), new RepoNormalizer(), $sut]));

        self::assertEquals(
            new Event(
                id:       4,
                type:     EventType::PULL_REQUEST,
                actor:    new Actor(id: 1, login: 'Kocal', url: 'https://github.com/Kocal', avatarUrl: 'https://avatars.githubusercontent.com/u/1'),
                repo:     new Repo(id: 1, name: 'Kocal/BiomeJsBundle', url: 'https://github.com/Kocal/BiomeJsBundle'),
                payload:  ['action' => 'opened'],
                createAt: new \DateTimeImmutable('2021-08-01T00:00:00Z'),
                comment:  null,
            ),
            $sut->denormalize($normalizedData, Event::class, EventNormalizer::FORMAT_GITHUB_JSON_PAYLOAD)
        );
    }

    private static function provideMappingFromGitHubEventTypeToOurEventType(): iterable
    {
        yield [EventType::COMMENT, 'CommitCommentEvent'];
        yield [new \InvalidArgumentException('Unsupported event type "CreateEvent".'), 'CreateEvent'];
        yield [new \InvalidArgumentException('Unsupported event type "DeleteEvent".'), 'DeleteEvent'];
        yield [new \InvalidArgumentException('Unsupported event type "ForkEvent".'), 'ForkEvent'];
        yield [new \InvalidArgumentException('Unsupported event type "GollumEvent".'), 'GollumEvent'];
        yield [EventType::COMMENT, 'IssueCommentEvent'];
        yield [new \InvalidArgumentException('Unsupported event type "IssuesEventMemberEvent".'), 'IssuesEventMemberEvent'];
        yield [new \InvalidArgumentException('Unsupported event type "PublicEvent".'), 'PublicEvent'];
        yield [EventType::PULL_REQUEST, 'PullRequestEvent'];
        yield [new \InvalidArgumentException('Unsupported event type "PullRequestReviewEvent".'), 'PullRequestReviewEvent'];
        yield [EventType::COMMENT, 'PullRequestReviewCommentEvent'];
        yield [new \InvalidArgumentException('Unsupported event type "PullRequestReviewThreadEvent".'), 'PullRequestReviewThreadEvent'];
        yield [EventType::COMMIT, 'PushEvent'];
        yield [new \InvalidArgumentException('Unsupported event type "ReleaseEvent".'), 'ReleaseEvent'];
        yield [new \InvalidArgumentException('Unsupported event type "SponsorshipEventWatchEvent".'), 'SponsorshipEventWatchEvent'];
    }

    /**
     * @dataProvider provideMappingFromGitHubEventTypeToOurEventType
     */
    public function testMappingFromGitHubEventTypeToOurEventType(string|\Exception $expected, string $eventType): void
    {
        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }
        
        $normalizedData = [
            'type' => $eventType,
            'id' => 4,
            'actor' => [
                'id' => 1,
                'login' => 'Kocal',
                'url' => 'https://github.com/Kocal',
                'avatar_url' => 'https://avatars.githubusercontent.com/u/1',
            ],
            'repo' => [
                'id' => 1,
                'name' => 'Kocal/BiomeJsBundle',
                'url' => 'https://github.com/Kocal/BiomeJsBundle',
            ],
            'payload' => [
                'action' => 'opened',
            ],
            'created_at' => '2021-08-01T00:00:00Z',
        ];

        $sut = new EventNormalizer();
        $sut->setDenormalizer(new Serializer([new ActorNormalizer(), new RepoNormalizer(), $sut]));

        $event = $sut->denormalize($normalizedData, Event::class, EventNormalizer::FORMAT_GITHUB_JSON_PAYLOAD);
        
        self::assertSame($expected, $event->type());
    }
}
