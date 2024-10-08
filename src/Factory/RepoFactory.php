<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Repo;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Repo>
 */
final class RepoFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Repo::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'id' => self::faker()->unique()->randomNumber(),
            'name' => self::faker()->name,
            'url' => self::faker()->url,
        ];
    }
}
