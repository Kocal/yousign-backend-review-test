<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Event;
use App\Entity\EventType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Event>
 */
final class EventFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Event::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'id' => self::faker()->unique()->randomNumber(),
            'type' => self::faker()->randomElement(EventType::cases()),
            'actor' => ActorFactory::createOne(),
            'repo' => RepoFactory::createOne(),
            'payload' => [],
            'createAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'comment' => null,
        ];
    }
}
