<?php
declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Repo;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class RepoNormalizer implements DenormalizerInterface, DenormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use DenormalizerAwareTrait;

    public const FORMAT_GITHUB_JSON_PAYLOAD = 'github_json_payload';

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): Repo
    {
        return new Repo(
            id: $data['id'],
            name: $data['name'],
            url: $data['url'],
        );
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return $type === Repo::class && $format === self::FORMAT_GITHUB_JSON_PAYLOAD;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
