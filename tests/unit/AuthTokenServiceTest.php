<?php

namespace Tests\Unit;

use App\Services\AuthTokenService;
use PHPUnit\Framework\TestCase;

class AuthTokenServiceTest extends TestCase
{
    public function testGeneratedTokensAreOpaqueUrlSafeAndUnique(): void
    {
        $method = new \ReflectionMethod(AuthTokenService::class, 'randomToken');
        $method->setAccessible(true);
        $service = new AuthTokenService();

        $first = $method->invoke($service);
        $second = $method->invoke($service);

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]{64}$/', $first);
        $this->assertNotSame($first, $second);
    }

    public function testGeneratedTokenIdsAreVersionFourUuids(): void
    {
        $method = new \ReflectionMethod(AuthTokenService::class, 'uuid');
        $method->setAccessible(true);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $method->invoke(new AuthTokenService())
        );
    }
}
