<?php

namespace App\Tests\Func;

use App\Story\EventStory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class EventControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    private static KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        static::$client = static::createClient();

        EventStory::load();
    }

    public function testUpdateShouldReturnEmptyResponse(): void
    {
        $client = static::$client;

        $client->request(
            'PUT',
            \sprintf('/api/event/%d/update', EventStory::EVENT_1_ID),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['comment' => 'It‘s a test comment !!!!!!!!!!!!!!!!!!!!!!!!!!!'], flags: \JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(204);
    }

    public function testUpdateShouldReturnHttpNotFoundResponse(): void
    {
        $client = static::$client;

        $client->request(
            'PUT',
            \sprintf('/api/event/%d/update', 7897897897),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['comment' => 'It‘s a test comment !!!!!!!!!!!!!!!!!!!!!!!!!!!'], flags: \JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(404);

        $expectedJson = <<<JSON
              {
                "message":"Event identified by 7897897897 not found !"
              }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $client->getResponse()->getContent() ?: '');
    }

    /**
     * @dataProvider providePayloadViolations
     */
    public function testUpdateShouldReturnBadRequest(string $payload, string $expectedResponse): void
    {
        $client = static::$client;

        $client->request(
            'PUT',
            \sprintf('/api/event/%d/update', EventStory::EVENT_1_ID),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $client->getResponse()->getContent() ?: '');
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public function providePayloadViolations(): iterable
    {
        yield 'comment too short' => [
            <<<JSON
              {
                "comment": "short"

            }
            JSON,
            <<<JSON
                {
                    "message": "This value is too short. It should have 20 characters or more."
                }
            JSON
        ];
    }
}
