<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\LaminasAuthentication;

use Laminas\Authentication\AuthenticationService;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\LaminasAuthentication\LaminasAuthentication;
use Mezzio\Authentication\LaminasAuthentication\LaminasAuthenticationFactory;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;

class LaminasAuthenticationFactoryTest extends TestCase
{
    /** @var AuthenticationService&MockObject */
    private $authService;

    /** @var ResponseInterface&MockObject */
    private $responsePrototype;

    /** @var callable */
    private $responseFactory;

    /** @var UserInterface&MockObject */
    private $userPrototype;

    /** @var callable */
    private $userFactory;

    public function setUp(): void
    {
        $this->authService       = $this->createMock(AuthenticationService::class);
        $this->responsePrototype = $this->createMock(ResponseInterface::class);
        $this->responseFactory   = function () {
            return $this->responsePrototype;
        };
        $this->userPrototype     = $this->createMock(UserInterface::class);
        $this->userFactory       = function () {
            return $this->userPrototype;
        };
    }

    public function testInvokeWithEmptyContainer()
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new LaminasAuthenticationFactory();
        $this->expectException(InvalidConfigException::class);
        $factory($container);
    }

    public function testInvokeWithContainerEmptyConfig()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('has')
            ->will($this->returnValueMap([
                [AuthenticationService::class, true],
                [ResponseInterface::class, true],
                [UserInterface::class, true],
            ]));
        $container
            ->method('get')
            ->will($this->returnValueMap([
                [AuthenticationService::class, $this->authService],
                [ResponseInterface::class, $this->responseFactory],
                [UserInterface::class, $this->userFactory],
                ['config', []],
            ]));

        $factory = new LaminasAuthenticationFactory();

        $this->expectException(InvalidConfigException::class);
        $factory($container);
    }

    public function testInvokeWithContainerAndConfig()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('has')
            ->will($this->returnValueMap([
                [AuthenticationService::class, true],
                [ResponseInterface::class, true],
                [UserInterface::class, true],
            ]));
        $container
            ->method('get')
            ->will($this->returnValueMap([
                [AuthenticationService::class, $this->authService],
                [ResponseInterface::class, $this->responseFactory],
                [UserInterface::class, $this->userFactory],
                [
                    'config',
                    [
                        'authentication' => ['redirect' => '/login'],
                    ],
                ],
            ]));

        $factory               = new LaminasAuthenticationFactory();
        $laminasAuthentication = $factory($container);
        $this->assertInstanceOf(LaminasAuthentication::class, $laminasAuthentication);
        $this->assertResponseFactoryReturns($this->responsePrototype, $laminasAuthentication);
    }

    public function assertResponseFactoryReturns(
        ResponseInterface $expected,
        LaminasAuthentication $service
    ): void {
        $r = new ReflectionProperty($service, 'responseFactory');
        $r->setAccessible(true);
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $r->getValue($service);
        $this->responsePrototype
            ->expects($this->once())
            ->method('withStatus')
            ->with(200, '')
            ->willReturnSelf();
        Assert::assertSame($expected, $responseFactory->createResponse());
    }
}
