<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-laminasauthentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-laminasauthentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-laminasauthentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\Adapter;

use Laminas\Authentication\Adapter\AbstractAdapter;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\LaminasAuthentication\LaminasAuthentication;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LaminasAuthenticationTest extends TestCase
{
    use ProphecyTrait;

    /** @var ServerRequestInterface|ObjectProphecy */
    private $request;

    /** @var AuthenticationService|ObjectProphecy */
    private $authService;

    /** @var UserInterface|ObjectProphecy */
    private $authenticatedUser;

    /** @var callable */
    private $responseFactory;

    /** @var callable */
    private $userFactory;

    /** @var UserInterface|ObjectProphecy */
    private $userPrototype;

    public function setUp(): void
    {
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->authService = $this->prophesize(AuthenticationService::class);
        $this->authenticatedUser = $this->prophesize(UserInterface::class);
        $this->responseFactory = function () {
            return $this->prophesize(ResponseInterface::class)->reveal();
        };
        $this->userPrototype = $this->prophesize(UserInterface::class);
        $this->userFactory = function () {
            return $this->userPrototype->reveal();
        };
    }

    public function testConstructor()
    {
        $laminasAuthentication = new LaminasAuthentication(
            $this->authService->reveal(),
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $this->assertInstanceOf(AuthenticationInterface::class, $laminasAuthentication);
    }

    public function testAuthenticateWithGetMethodAndIdentity()
    {
        $this->request->getMethod()->willReturn('GET');
        $this->authService->hasIdentity()->willReturn(true);
        $this->authService->getIdentity()->willReturn('foo');

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService->reveal(),
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $result = $laminasAuthentication->authenticate($this->request->reveal());
        $this->assertInstanceOf(UserInterface::class, $result);
    }

    public function testAuthenticateWithGetMethodAndNoIdentity()
    {
        $this->request->getMethod()->willReturn('GET');
        $this->authService->hasIdentity()->willReturn(false);

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService->reveal(),
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $this->assertNull($laminasAuthentication->authenticate($this->request->reveal()));
    }

    public function testAuthenticateWithPostMethodAndNoParams()
    {
        $this->request->getMethod()->willReturn('POST');
        $this->request->getParsedBody()->willReturn([]);

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService->reveal(),
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $this->assertNull($laminasAuthentication->authenticate($this->request->reveal()));
    }

    public function testAuthenticateWithPostMethodAndNoValidCredential()
    {
        //not authenticated
        $this->authService->hasIdentity()->willReturn(false);

        $this->request->getMethod()->willReturn('POST');
        $this->request->getParsedBody()->willReturn([
            'username' => 'foo',
            'password' => 'bar',
        ]);
        $adapter = $this->prophesize(AbstractAdapter::class);
        $adapter->setIdentity('foo')->willReturn(null);
        $adapter->setCredential('bar')->willReturn();

        $this->authService
            ->getAdapter()
            ->willReturn($adapter->reveal());
        $result = $this->prophesize(Result::class);
        $result->isValid()->willReturn(false);

        $this->authService
            ->authenticate()
            ->willReturn($result);

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService->reveal(),
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $this->assertNull($laminasAuthentication->authenticate($this->request->reveal()));
    }

    public function testAuthenticateWithPostMethodAndValidCredential()
    {
        //not authenticated
        $this->authService->hasIdentity()->willReturn(false);

        $this->request->getMethod()->willReturn('POST');
        $this->request->getParsedBody()->willReturn([
            'username' => 'foo',
            'password' => 'bar',
        ]);
        $adapter = $this->prophesize(AbstractAdapter::class);
        $adapter->setIdentity('foo')->willReturn(null);
        $adapter->setCredential('bar')->willReturn();

        $this->authService
            ->getAdapter()
            ->willReturn($adapter->reveal());
        $result = $this->prophesize(Result::class);
        $result->isValid()->willReturn(true);
        $result->getIdentity()->willReturn('foo');

        $this->authService
            ->authenticate()
            ->willReturn($result);

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService->reveal(),
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $result = $laminasAuthentication->authenticate($this->request->reveal());
        $this->assertInstanceOf(UserInterface::class, $result);
    }

    public function testAuthenticateWithPostMethodAndNoValidCredentialAndAlreadyAuthenticated()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authService->getIdentity()->willReturn('string');

        $this->request->getMethod()->willReturn('POST');
        $this->request->getParsedBody()->willReturn([
            'username' => 'foo',
            'password' => 'bar',
        ]);
        $adapter = $this->prophesize(AbstractAdapter::class);
        $adapter->setIdentity('foo')->willReturn(null);
        $adapter->setCredential('bar')->willReturn();

        $this->authService
            ->getAdapter()
            ->willReturn($adapter->reveal());
        $result = $this->prophesize(Result::class);
        $result->isValid()->willReturn(false);

        $this->authService
            ->authenticate()
            ->willReturn($result);

        $this->userPrototype->getIdentity()->willReturn('string');

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService->reveal(),
            [],
            $this->responseFactory,
            $this->userFactory
        );
        $identity = $laminasAuthentication->authenticate($this->request->reveal());
        $this->assertInstanceOf(UserInterface::class, $identity);
        $this->assertEquals('string', $identity->getIdentity());
    }

    public function testAuthenticateWithPostMethodAndValidCredentialAndAlreadyAuthenticated()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authService->getIdentity()->willReturn('string');

        $this->request->getMethod()->willReturn('POST');

        $laminasAuthentication = new LaminasAuthentication(
            $this->authService->reveal(),
            [],
            $this->responseFactory,
            $this->userFactory
        );

        $result = $laminasAuthentication->authenticate($this->request->reveal());

        $this->assertInstanceOf(UserInterface::class, $result);
    }
}
