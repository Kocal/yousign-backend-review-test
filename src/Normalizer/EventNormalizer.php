<?php
declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class EventNormalizer implements DenormalizerInterface, DenormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use DenormalizerAwareTrait;

    public const FORMAT_GITHUB_JSON_PAYLOAD = 'github_json_payload';
    public const CONTEXT_REPO_INSTANCE = 'repo_instance';
    public const CONTEXT_ACTOR_INSTANCE = 'actor_instance';

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): Event
    {
        $type = match ($data['type']) {
            'CommitCommentEvent', 'IssueCommentEvent', 'PullRequestReviewCommentEvent' => EventType::COMMENT,
            'PullRequestEvent' => EventType::PULL_REQUEST,
            'PushEvent' => EventType::COMMIT,
            default => throw new \InvalidArgumentException(sprintf('Unsupported event type "%s".', $data['type'])),
        };

        return new Event(
            id: (int)$data['id'],
            type: $type,
            actor: $context[self::CONTEXT_ACTOR_INSTANCE] ?? $this->denormalizer->denormalize($data['actor'], Actor::class, $format, $context),
            repo: $context[self::CONTEXT_REPO_INSTANCE] ?? $this->denormalizer->denormalize($data['repo'], Repo::class, $format, $context),
            payload: $data['payload'],
            createAt: new \DateTimeImmutable($data['created_at']),
            // TODO: is it supposed to be the comment's body (if event's type is comment) accessible through "payload",
            // or something updatable from "api_commit_update" route?
            comment: null,
        );
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return $type === Event::class && $format === self::FORMAT_GITHUB_JSON_PAYLOAD;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
