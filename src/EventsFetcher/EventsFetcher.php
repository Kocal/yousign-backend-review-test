<?php
declare(strict_types=1);

namespace App\EventsFetcher;

interface EventsFetcher
{
    /**
     * @param \DateTimeInterface $date
     * @return iterable<array{
     *     id: string,
     *     type: 'CommitCommentEvent'|'CreateEvent'|'DeleteEvent'|'ForkEvent'|'GollumEvent'|'IssueCommentEvent'|'IssuesEventMemberEvent'|'PublicEvent'|'PullRequestEvent'|'PullRequestReviewEvent'|'PullRequestReviewCommentEvent'|'PullRequestReviewThreadEvent'|'PushEventReleaseEvent'|'SponsorshipEventWatchEvent',
     *     actor: array{id: int, login: string, display_login: string, gravatar_id: string, url: string, avatar_url: string},   
     *     repo: array{id: int, name: string, url: string},
     *     payload: array<mixed>,
     *     public: bool,
     *     created_at: string
     * }>
     */
    public function fetchForDate(\DateTimeInterface $date): iterable;
}
