<?php
declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Entity\Event;
use App\Message\ImportGitHubEventsForDate;
use App\Message\ImportGitHubEventsForDateHandler;
use App\Normalizer\ActorNormalizer;
use App\Normalizer\EventNormalizer;
use App\Normalizer\RepoNormalizer;
use App\Repository\ReadActorRepository;
use App\Repository\ReadEventRepository;
use App\Repository\ReadRepoRepository;
use App\Repository\WriteEventRepository;
use App\Tests\Toolbox\EventsFetcher\ArrayJsonEventsFetcher;
use App\Tests\Toolbox\Logger\MockLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

final  class ImportGitHubEventsForDateHandlerTest extends TestCase
{
    public function testInvoke(): void
    {
        $sut = new ImportGitHubEventsForDateHandler(
            eventsFetcher: new ArrayJsonEventsFetcher([
                '{"id":"34507485722","type":"PushEvent","actor":{"id":41898282,"login":"github-actions[bot]","display_login":"github-actions","gravatar_id":"","url":"https://api.github.com/users/github-actions[bot]","avatar_url":"https://avatars.githubusercontent.com/u/41898282?"},"repo":{"id":307884926,"name":"Shadow-Java/Shadow-Java","url":"https://api.github.com/repos/Shadow-Java/Shadow-Java"},"payload":{"repository_id":307884926,"push_id":16469438072,"size":1,"distinct_size":1,"ref":"refs/heads/main","head":"256cb173864459d89aad950e364c833a588547c9","before":"fae0c72768263eaad2028b5441caa759d17e65cc","commits":[{"sha":"256cb173864459d89aad950e364c833a588547c9","author":{"email":"41898282+github-actions[bot]@users.noreply.github.com","name":"GitHub Actions"},"message":"new stats received :dog:","distinct":true,"url":"https://api.github.com/repos/Shadow-Java/Shadow-Java/commits/256cb173864459d89aad950e364c833a588547c9"}]},"public":true,"created_at":"2024-01-02T00:59:58Z"}',
                '{"id":"34507485723","type":"DeleteEvent","actor":{"id":1732331,"login":"injms","display_login":"injms","gravatar_id":"","url":"https://api.github.com/users/injms","avatar_url":"https://avatars.githubusercontent.com/u/1732331?"},"repo":{"id":314056609,"name":"injms/qck","url":"https://api.github.com/repos/injms/qck"},"payload":{"ref":"dependabot/npm_and_yarn/i18next-23.7.13","ref_type":"branch","pusher_type":"user"},"public":true,"created_at":"2024-01-02T00:59:58Z"}',
                '{"id":"34507485724","type":"CreateEvent","actor":{"id":18418647,"login":"dilansalinda","display_login":"dilansalinda","gravatar_id":"","url":"https://api.github.com/users/dilansalinda","avatar_url":"https://avatars.githubusercontent.com/u/18418647?"},"repo":{"id":737921890,"name":"dilansalinda/dilansalinda.github.io","url":"https://api.github.com/repos/dilansalinda/dilansalinda.github.io"},"payload":{"ref":"change-the-layout","ref_type":"branch","master_branch":"master","description":null,"pusher_type":"user"},"public":true,"created_at":"2024-01-02T00:59:58Z"}',
                '{"id":"34507485725","type":"PushEvent","actor":{"id":68137112,"login":"joshwithers","display_login":"joshwithers","gravatar_id":"","url":"https://api.github.com/users/joshwithers","avatar_url":"https://avatars.githubusercontent.com/u/68137112?"},"repo":{"id":545413512,"name":"joshwithers/microblog","url":"https://api.github.com/repos/joshwithers/microblog"},"payload":{"repository_id":545413512,"push_id":16469438073,"size":1,"distinct_size":1,"ref":"refs/heads/main","head":"820881812274eb70e14866bd58466d3fc7104377","before":"2c6870895e9477ce738e2c0ffb8d085eae35ca38","commits":[{"sha":"820881812274eb70e14866bd58466d3fc7104377","author":{"email":"68137112+joshwithers@users.noreply.github.com","name":"Josh Withers"},"message":"Archiving from Micro.blog.","distinct":true,"url":"https://api.github.com/repos/joshwithers/microblog/commits/820881812274eb70e14866bd58466d3fc7104377"}]},"public":true,"created_at":"2024-01-02T00:59:58Z"}',
            ]),
            denormalizer: new Serializer([new EventNormalizer(), new ActorNormalizer(), new RepoNormalizer()]),
            writeEventRepository: $writeEventRepositoryMock = $this->createMock(WriteEventRepository::class),
            readEventRepository: $readEventRepositoryMock = $this->createMock(ReadEventRepository::class),
            readActorRepository: $readActorRepositoryMock = $this->createMock(ReadActorRepository::class),
            readRepoRepository: $readRepoRepositoryMock = $this->createMock(ReadRepoRepository::class),
            logger: $logger = new MockLogger(),
        );
        
        $readEventRepositoryMock
            ->expects(self::exactly(2)) // 2 events are allowed to be imported
            ->method('exist')
            ->willReturnOnConsecutiveCalls(false, false, false, false);
        
        $readActorRepositoryMock
            ->expects(self::exactly(2))
            ->method('find')
            ->willReturn(null);
        
        $readRepoRepositoryMock
            ->expects(self::exactly(2))
            ->method('find')
            ->willReturn(null);

        $writeEventRepositoryMock
            ->expects(self::exactly(2 + 1)) // 2 events, and 1 call to save the batch (if any)
            ->method('save')
            ->withConsecutive(
                [$this->callback(fn(Event $event) => $event->id() === 34507485722), false],
                [$this->callback(fn(Event $event) => $event->id() === 34507485725), false],
                [$this->callback(fn(Event $event) => $event->id() === 34507485725), true],
          );
        
        $sut(new ImportGitHubEventsForDate(
            date: new \DateTimeImmutable('2024-01-02'),
        ));
        
        $logger->assertLogged('info', 'Handling import of GitHub events for {date}');
        $logger->assertLogged('notice', 'Importing event {id} of type {type}.', ['id' => '34507485722', 'type' => 'PushEvent']);
        $logger->assertLogged('info', 'Event {id} of type {type} is not allowed, skipping.', ['id' => '34507485723', 'type' => 'DeleteEvent']);
        $logger->assertLogged('info', 'Event {id} of type {type} is not allowed, skipping.', ['id' => '34507485724', 'type' => 'CreateEvent']);
        $logger->assertLogged('notice', 'Importing event {id} of type {type}.', ['id' => '34507485725', 'type' => 'PushEvent']);
    }
}
