<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-laminasauthentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-laminasauthentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-laminasauthentication/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Authentication\Adapter;

use Laminas\Authentication\Adapter\AbstractAdapter;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\LaminasAuthentication\LaminasAuthentication;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LaminasAuthenticationTest extends TestCase
{
    /** @var ServerRequestInterface|ObjectProphecy */
    private $request;

    /** @var AuthenticationService|ObjectProphecy */
    private $authService;

    /** @var UserInterface|ObjectProphecy */
    private $authenticatedUser;

    /** @var callable */
    private $responseFactory;

    protected function setUp()
    {
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->authService = $this->prophesize(AuthenticationService::class);
        $this->authenticatedUser = $this->prophesize(UserInterface::class);
        $this->responseFactory = function () {
            return $this->prophesize(ResponseInterface::class)->reveal();
        };
    }

    public function testConstructor()
    {
        $laminasAuthentication = new LaminasAuthentication(
            $this->authService->reveal(),
            [],
            $this->responseFactory
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
            $this->responseFactory
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
            $this->responseFactory
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
            $this->responseFactory
        );
        $this->assertNull($laminasAuthentication->authenticate($this->request->reveal()));
    }

    public function testAuthenticateWithPostMethodAndNoValidCredential()
    {
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
            $this->responseFactory
        );
        $this->assertNull($laminasAuthentication->authenticate($this->request->reveal()));
    }

    public function testAuthenticateWithPostMethodAndValidCredential()
    {
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
            $this->responseFactory
        );
        $result = $laminasAuthentication->authenticate($this->request->reveal());
        $this->assertInstanceOf(UserInterface::class, $result);
    }
}
