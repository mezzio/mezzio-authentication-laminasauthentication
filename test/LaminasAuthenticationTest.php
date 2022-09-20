<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\Adapter;

use Laminas\Authentication\Adapter\AbstractAdapter;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\LaminasAuthentication\LaminasAuthentication;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LaminasAuthenticationTest extends TestCase
{
    /** @var ServerRequestInterface&MockObject */
    private $request;

    /** @var AuthenticationService&MockObject */
    private $authService;

    /** @var callable */
    private $responseFactory;

    /** @var callable */
    private $userFactory;

    /** @var UserInterface&MockObject */
    private $userPrototype;

    public function setUp(): void
    {
        $this->request         = $this->createMock(ServerRequestInterface::class);
        $this->authService     = $this->createMock(AuthenticationService::class);
        $this->responseFactory = fn(): ResponseInterface => $this->createMock(ResponseInterface::class);
        $this->userPrototype   = $this->createMock(UserInterface::class);
        $this->userFactory     = fn(): UserInterface => $this->userPrototype;
    }

    public function testConstructor(): void
    {
        $laminasAuthentication = new LaminasAuthentication(
            $this->authService,
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $this->assertInstanceOf(AuthenticationInterface::class, $laminasAuthentication);
    }

    public function testAuthenticateWithGetMethodAndIdentity(): void
    {
        $this->request->method('getMethod')->willReturn('GET');
        $this->authService->method('hasIdentity')->willReturn(true);
        $this->authService->method('getIdentity')->willReturn('foo');

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService,
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $result                = $laminasAuthentication->authenticate($this->request);
        $this->assertInstanceOf(UserInterface::class, $result);
    }

    public function testAuthenticateWithGetMethodAndNoIdentity(): void
    {
        $this->request->method('getMethod')->willReturn('GET');
        $this->authService->method('hasIdentity')->willReturn(false);

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService,
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $this->assertNull($laminasAuthentication->authenticate($this->request));
    }

    public function testAuthenticateWithPostMethodAndNoParams(): void
    {
        $this->request->method('getMethod')->willReturn('POST');
        $this->request->method('getParsedBody')->willReturn([]);

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService,
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $this->assertNull($laminasAuthentication->authenticate($this->request));
    }

    public function testAuthenticateWithPostMethodAndNoValidCredential(): void
    {
        //not authenticated
        $this->authService->method('hasIdentity')->willReturn(false);

        $this->request->method('getMethod')->willReturn('POST');
        $this->request->method('getParsedBody')->willReturn([
            'username' => 'foo',
            'password' => 'bar',
        ]);
        $adapter = $this->createMock(AbstractAdapter::class);
        $adapter->method('setIdentity')->with('foo')->willReturn(null);
        $adapter->method('setCredential')->with('bar')->willReturn(null);

        $this->authService
            ->method('getAdapter')
            ->willReturn($adapter);
        $result = $this->createMock(Result::class);
        $result->method('isValid')->willReturn(false);

        $this->authService
            ->method('authenticate')
            ->willReturn($result);

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService,
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $this->assertNull($laminasAuthentication->authenticate($this->request));
    }

    public function testAuthenticateWithPostMethodAndValidCredential(): void
    {
        //not authenticated
        $this->authService->method('hasIdentity')->willReturn(false);

        $this->request->method('getMethod')->willReturn('POST');
        $this->request->method('getParsedBody')->willReturn([
            'username' => 'foo',
            'password' => 'bar',
        ]);
        $adapter = $this->createMock(AbstractAdapter::class);
        $adapter->method('setIdentity')->with('foo')->willReturn(null);
        $adapter->method('setCredential')->with('bar')->willReturn(null);

        $this->authService
            ->method('getAdapter')
            ->willReturn($adapter);
        $result = $this->createMock(Result::class);
        $result->method('isValid')->willReturn(true);
        $result->method('getIdentity')->willReturn('foo');

        $this->authService
            ->method('authenticate')
            ->willReturn($result);

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService,
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $result                = $laminasAuthentication->authenticate($this->request);
        $this->assertInstanceOf(UserInterface::class, $result);
    }

    public function testAuthenticateWithPostMethodAndNoValidCredentialAndAlreadyAuthenticated(): void
    {
        $this->authService->method('hasIdentity')->willReturn(true);
        $this->authService->method('getIdentity')->willReturn('string');

        $this->request->method('getMethod')->willReturn('POST');
        $this->request->method('getParsedBody')->willReturn([
            'username' => 'foo',
            'password' => 'bar',
        ]);
        $adapter = $this->createMock(AbstractAdapter::class);
        $adapter->method('setIdentity')->with('foo')->willReturn(null);
        $adapter->method('setCredential')->with('bar')->willReturn(null);

        $this->authService
            ->method('getAdapter')
            ->willReturn($adapter);
        $result = $this->createMock(Result::class);
        $result->method('isValid')->willReturn(false);

        $this->authService
            ->method('authenticate')
            ->willReturn($result);

        $this->userPrototype->method('getIdentity')->willReturn('string');

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService,
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $identity              = $laminasAuthentication->authenticate($this->request);
        $this->assertInstanceOf(UserInterface::class, $identity);
        $this->assertEquals('string', $identity->getIdentity());
    }

    public function testAuthenticateWithPostMethodAndValidCredentialAndAlreadyAuthenticated(): void
    {
        $this->authService->method('hasIdentity')->willReturn(true);
        $this->authService->method('getIdentity')->willReturn('string');

        $this->request->method('getMethod')->willReturn('POST');

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService,
            [],
            $this->responseFactory,
            $this->userFactory
        );

        $result = $laminasAuthentication->authenticate($this->request);

        $this->assertInstanceOf(UserInterface::class, $result);
    }
}
