<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\LaminasAuthentication;

use Mezzio\Authentication\LaminasAuthentication\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    private ConfigProvider $provider;

    public function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArrayWithExpectedStructure(): void
    {
        $config = ($this->provider)();
        self::assertArrayHasKey('dependencies', $config);
        self::assertArrayHasKey('authentication', $config);
        self::assertIsArray($config['dependencies']);
        self::assertIsArray($config['authentication']);
    }
}
