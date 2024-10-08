<?php
declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Actor;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class ActorNormalizer implements DenormalizerInterface, DenormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use DenormalizerAwareTrait;

    public const FORMAT_GITHUB_JSON_PAYLOAD = 'github_json_payload';

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): Actor
    {
        return new Actor(
            id: (int) $data['id'],
            login: $data['login'],
            url: $data['url'],
            avatarUrl: $data['avatar_url'],
        );
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return $type === Actor::class && $format === self::FORMAT_GITHUB_JSON_PAYLOAD;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
