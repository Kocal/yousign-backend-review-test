<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Actor;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Actor>
 */
final class ActorFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Actor::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'id' => self::faker()->unique()->randomNumber(),
            'login' => self::faker()->userName,
            'url' => self::faker()->url,
            'avatarUrl' => self::faker()->imageUrl(),
        ];
    }
}
