<?php
declare(strict_types=1);

namespace App\Tests\Unit\Normalizer;

use App\Entity\Actor;
use App\Normalizer\ActorNormalizer;
use PHPUnit\Framework\TestCase;

final class ActorNormalizerTest extends TestCase
{
    private static function provideSupportsDenormalization(): iterable
    {
        yield [true, [], Actor::class, ActorNormalizer::FORMAT_GITHUB_JSON_PAYLOAD];
        yield [false, [], Actor::class, 'json'];
        yield [false, [], 'App\\Entity\\Repository', ActorNormalizer::FORMAT_GITHUB_JSON_PAYLOAD];
    }

    /**
     * @dataProvider provideSupportsDenormalization
     */
    public function testSupportsDenormalization(bool $expected, mixed $data, string $type, string $format = null): void
    {
        $sut = new ActorNormalizer();

        self::assertSame($expected, $sut->supportsDenormalization($data, $type, $format));
    }
    
    public function testDenormalization(): void
    {
        $normalizedData = [
            'id' => 1,
            'login' => 'Kocal',
            'url' => 'https://github.com/Kocal',
            'avatar_url' => 'https://avatars.githubusercontent.com/u/1',
        ];
            
        $sut = new ActorNormalizer();
        
        self::assertEquals(
            new Actor(id: 1, login: 'Kocal', url: 'https://github.com/Kocal', avatarUrl: 'https://avatars.githubusercontent.com/u/1'),
            $sut->denormalize($normalizedData, Actor::class, ActorNormalizer::FORMAT_GITHUB_JSON_PAYLOAD)
        );
    }
}
