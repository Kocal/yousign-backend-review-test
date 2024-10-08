<?php

namespace App\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * TODO: Replace with a native PHP Enum, maybe upgrade Doctrine too.
 *
 * @extends AbstractEnumType<string, string>
 */
class EventType extends AbstractEnumType
{
    public const COMMIT = 'COM';

    public const COMMENT = 'MSG';

    public const PULL_REQUEST = 'PR';

    protected static array $choices = [
        self::COMMIT => 'Commit',
        self::COMMENT => 'Comment',
        self::PULL_REQUEST => 'Pull Request',
    ];
}
