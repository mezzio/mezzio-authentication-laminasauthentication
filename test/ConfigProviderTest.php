<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\LaminasAuthentication;

use Mezzio\Authentication\LaminasAuthentication\ConfigProvider;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray()
    {
        $config = ($this->provider)();
        $this->assertInternalType('array', $config);
        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config)
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertInternalType('array', $config['dependencies']);
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsAuthenticationConfig(array $config)
    {
        $this->assertArrayHasKey('authentication', $config);
        $this->assertInternalType('array', $config['authentication']);
    }

    private static function assertInternalType(string $expected, $actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType($expected),
            $message
        );
    }
}
