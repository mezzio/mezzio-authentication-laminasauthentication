<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-laminasauthentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-laminasauthentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-laminasauthentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\LaminasAuthentication;

use Mezzio\Authentication\LaminasAuthentication\ConfigProvider;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress MissingConstructor
 */
class ConfigProviderTest extends TestCase
{
    /** @var ConfigProvider*/
    private $provider;

    /**
     * @psalm-suppress UndefinedThisPropertyAssignment
     */
    public function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    /**
     * @psalm-suppress MissingReturnType
     */
    public function testInvocationReturnsArray()
    {
        $config = ($this->provider)();
        $this->assertInternalType('array', $config);
        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     * @psalm-suppress MissingReturnType
     */
    public function testReturnedArrayContainsDependencies(array $config)
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertInternalType('array', $config['dependencies']);
    }

    /**
     * @depends testInvocationReturnsArray
     * @psalm-suppress MissingReturnType
     */
    public function testReturnedArrayContainsAuthenticationConfig(array $config)
    {
        $this->assertArrayHasKey('authentication', $config);
        $this->assertInternalType('array', $config['authentication']);
    }

    /**
     * @psalm-suppress MissingParamType
     */
    private static function assertInternalType(string $expected, $actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType($expected),
            $message
        );
    }
}
