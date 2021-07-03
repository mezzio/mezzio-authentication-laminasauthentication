<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-laminasauthentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-laminasauthentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-laminasauthentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\LaminasAuthentication;

use Laminas\Authentication\AuthenticationService;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\LaminasAuthentication\LaminasAuthentication;
use Mezzio\Authentication\LaminasAuthentication\LaminasAuthenticationFactory;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @psalm-suppress MissingConstructor
 */
class LaminasAuthenticationFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var LaminasAuthentication */
    private $factory;

    /** @var AuthenticationService|ObjectProphecy */
    private $authService;

    /** @var ResponseInterface|ObjectProphecy */
    private $responsePrototype;

    /** @var callable */
    private $responseFactory;

    /** @var UserInterface|ObjectProphecy */
    private $userPrototype;

    /** @var callable */
    private $userFactory;

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress MissingClosureReturnType
     */
    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new LaminasAuthenticationFactory();
        $this->authService = $this->prophesize(AuthenticationService::class);
        $this->responsePrototype = $this->prophesize(ResponseInterface::class);
        $this->responseFactory = function () {
            return $this->responsePrototype->reveal();
        };
        $this->userPrototype = $this->prophesize(UserInterface::class);
        $this->userFactory = function () {
            return $this->userPrototype->reveal();
        };
    }

    /**
     * @psalm-suppress MissingReturnType
     * @psalm-suppress InvalidFunctionCall
     * @psalm-suppress PossiblyUndefinedMethod
     */
    public function testInvokeWithEmptyContainer()
    {
        $this->expectException(InvalidConfigException::class);
        ($this->factory)($this->container->reveal());
    }

    /**
     * @psalm-suppress MissingReturnType
     * @psalm-suppress PossiblyInvalidMethodCall
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress InvalidFunctionCall
     * @psalm-suppress PossiblyUndefinedMethod
     */
    public function testInvokeWithContainerEmptyConfig()
    {
        $this->container
            ->has(AuthenticationService::class)
            ->willReturn(true);
        $this->container
            ->get(AuthenticationService::class)
            ->willReturn($this->authService->reveal());
        $this->container
            ->has(ResponseInterface::class)
            ->willReturn(true);
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn($this->responseFactory);
        $this->container
            ->has(UserInterface::class)
            ->willReturn(true);
        $this->container
            ->get(UserInterface::class)
            ->willReturn($this->userFactory);
        $this->container
            ->get('config')
            ->willReturn([]);

        $this->expectException(InvalidConfigException::class);
        ($this->factory)($this->container->reveal());
    }

    /**
     * @psalm-suppress MissingReturnType
     * @psalm-suppress PossiblyInvalidMethodCall
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress InvalidFunctionCall
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress MixedArgument
     */
    public function testInvokeWithContainerAndConfig()
    {
        $this->container
            ->has(AuthenticationService::class)
            ->willReturn(true);
        $this->container
            ->get(AuthenticationService::class)
            ->willReturn($this->authService->reveal());
        $this->container
            ->has(ResponseInterface::class)
            ->willReturn(true);
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn($this->responseFactory);
        $this->container
            ->has(UserInterface::class)
            ->willReturn(true);
        $this->container
            ->get(UserInterface::class)
            ->willReturn($this->userFactory);
        $this->container
            ->get('config')
            ->willReturn([
                'authentication' => ['redirect' => '/login'],
            ]);

        $laminasAuthentication = ($this->factory)($this->container->reveal());
        $this->assertInstanceOf(LaminasAuthentication::class, $laminasAuthentication);
        $this->assertResponseFactoryReturns($this->responsePrototype->reveal(), $laminasAuthentication);
    }

    /**
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedFunctionCall
     */
    public static function assertResponseFactoryReturns(
        ResponseInterface $expected,
        LaminasAuthentication $service
    ) : void {
        $r = new ReflectionProperty($service, 'responseFactory');
        $r->setAccessible(true);
        $responseFactory = $r->getValue($service);
        Assert::assertSame($expected, $responseFactory());
    }
}
