<?php
declare(strict_types=1);

namespace App\Message;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use App\EventsFetcher\EventsFetcher;
use App\Normalizer\EventNormalizer;
use App\Repository\ReadActorRepository;
use App\Repository\ReadEventRepository;
use App\Repository\ReadRepoRepository;
use App\Repository\WriteEventRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AsMessageHandler]
final class ImportGitHubEventsForDateHandler
{
    private const BATCH_SIZE = 25;

    public function __construct(
        private readonly EventsFetcher $eventsFetcher,
        private readonly DenormalizerInterface $denormalizer,
        private readonly WriteEventRepository $writeEventRepository,
        private readonly ReadEventRepository $readEventRepository,
        private readonly ReadActorRepository $readActorRepository,
        private readonly ReadRepoRepository $readRepoRepository,
        private readonly LoggerInterface|null $logger = new NullLogger(),
    ) {
    }

    public function __invoke(ImportGitHubEventsForDate $message): void
    {
        $this->logger->info('Handling import of GitHub events for {date}', ['date' => $message->getDate()]);

        $i = 0; 
        
        foreach ($this->eventsFetcher->fetchForDate($message->getDate()) as $eventPayload) {
            // TODO: There are some memory leaks in this block, I think it is not related to the way 
            // events are read from .gz files and "json_decode"-ed, but rather to the way we check if 
            // the event already exists in the database.
            //
            // Anyway, due to the very very very large amount of events to import, maybe we should
            // dispatch a new message for each event to import to Messenger...?
            // But it depends of the infra, if we use AWS SQS, it will be very expensive to dispatch a lot of messages.
            
            if (!in_array($eventPayload['type'], ['CommitCommentEvent', 'IssueCommentEvent', 'PullRequestEvent', 'PullRequestReviewCommentEvent', 'PushEvent'], true)) {
                $this->logger->info('Event {id} of type {type} is not allowed, skipping.', ['id' => $eventPayload['id'], 'type' => $eventPayload['type']]);
                continue;
            }

            if ($this->readEventRepository->exist((int)$eventPayload['id'])) {
                $this->logger->info('Event {id} already exists, skipping.', ['id' => $eventPayload['id']]);
                continue;
            }
            
            // TODO: Symfony 6.4+: Replace the Symfony Denormalizer process with Valinor
            $event = $this->denormalizer->denormalize($eventPayload, Event::class, EventNormalizer::FORMAT_GITHUB_JSON_PAYLOAD, [
                EventNormalizer::CONTEXT_REPO_INSTANCE => $this->readRepoRepository->find($eventPayload['repo']['id'])
                    ?? $this->denormalizer->denormalize($eventPayload['repo'], Repo::class, EventNormalizer::FORMAT_GITHUB_JSON_PAYLOAD),
                EventNormalizer::CONTEXT_ACTOR_INSTANCE => $this->readActorRepository->find($eventPayload['actor']['id'])
                    ?? $this->denormalizer->denormalize($eventPayload['actor'], Actor::class, EventNormalizer::FORMAT_GITHUB_JSON_PAYLOAD),
            ]);

            $this->logger->notice('Importing event {id} of type {type}.', ['id' => $eventPayload['id'], 'type' => $eventPayload['type']]);
            
            $i = $i >= self::BATCH_SIZE ? 0 : $i + 1;
            $this->writeEventRepository->save($event, flush: $i === 0);
            gc_collect_cycles();
        }

        // Force flush for the last batch if needed
        if ($i > 0 && isset($event)) {
            $this->writeEventRepository->save($event, flush: true);
            gc_collect_cycles();
        }
    }
}
