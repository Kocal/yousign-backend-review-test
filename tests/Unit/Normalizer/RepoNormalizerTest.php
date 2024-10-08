<?php
declare(strict_types=1);

namespace App\Tests\Unit\Normalizer;

use App\Entity\Repo;
use App\Normalizer\RepoNormalizer;
use PHPUnit\Framework\TestCase;

final class RepoNormalizerTest extends TestCase
{
    private static function provideSupportsDenormalization(): iterable
    {
        yield [true, [], Repo::class, RepoNormalizer::FORMAT_GITHUB_JSON_PAYLOAD];
        yield [false, [], Repo::class, 'json'];
        yield [false, [], 'App\\Entity\\Repository', RepoNormalizer::FORMAT_GITHUB_JSON_PAYLOAD];
    }

    /**
     * @dataProvider provideSupportsDenormalization
     */
    public function testSupportsDenormalization(bool $expected, mixed $data, string $type, string $format = null): void
    {
        $sut = new RepoNormalizer();

        self::assertSame($expected, $sut->supportsDenormalization($data, $type, $format));
    }
    
    public function testDenormalization(): void
    {
        $normalizedData = [
            'id' => 1,
            'name' => 'Kocal/BiomeJsBundle',
            'url' => 'https://github.com/Kocal/BiomeJsBundle',
        ];
            
        $sut = new RepoNormalizer();
        
        self::assertEquals(
            new Repo(id: 1, name: 'Kocal/BiomeJsBundle', url: 'https://github.com/Kocal/BiomeJsBundle'),
            $sut->denormalize($normalizedData, Repo::class, RepoNormalizer::FORMAT_GITHUB_JSON_PAYLOAD)
        );
    }
}
