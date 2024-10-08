<?php

declare(strict_types=1);

namespace App\Story;

use App\Entity\EventType;
use App\Factory\ActorFactory;
use App\Factory\EventFactory;
use App\Factory\RepoFactory;
use Zenstruck\Foundry\Story;

final class EventStory extends Story
{
    public const int EVENT_1_ID = 1;

    public const int ACTOR_1_ID = 1;

    public const int REPO_1_ID = 1;

    #[\Override]
    public function build(): void
    {
        EventFactory::createOne([
            'id' => self::EVENT_1_ID,
            'type' => EventType::COMMENT,
            'actor' => ActorFactory::createOne([
                'id' => self::ACTOR_1_ID,
                'login' => 'jdoe',
                'url' => 'https://api.github.com/users/jdoe',
                'avatarUrl' => 'https://avatars.githubusercontent.com/u/1?',
            ]),
            'repo' => RepoFactory::createOne([
                'id' => self::REPO_1_ID,
                'name' => 'yousign/test',
                'url' => 'https://api.github.com/repos/yousign/backend-test',
            ]),
            'createAt' => new \DateTimeImmutable(),
            'comment' => 'Test comment initiate by fixture ',
        ]);
    }
}
