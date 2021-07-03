<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-laminasauthentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-laminasauthentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-laminasauthentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication\LaminasAuthentication;

use Laminas\Authentication\AuthenticationService;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function strtoupper;

class LaminasAuthentication implements AuthenticationInterface
{
    /**
     * @var AuthenticationService
     */
    protected $auth;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var callable
     */
    protected $responseFactory;

    /**
     * @var callable
     */
    protected $userFactory;

    public function __construct(
        AuthenticationService $auth,
        array $config,
        callable $responseFactory,
        callable $userFactory
    ) {
        $this->auth = $auth;
        $this->config = $config;

        // Ensures type safety of the composed factory
        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            /** @var ResponseInterface */
            return $responseFactory();
        };

        // Ensures type safety of the composed factory
        $this->userFactory = function (
            string $identity,
            array $roles = [],
            array $details = []
        ) use ($userFactory) : UserInterface {
            /** @var UserInterface */
            return $userFactory($identity, $roles, $details);
        };
    }

    public function authenticate(ServerRequestInterface $request) : ?UserInterface
    {
        if (! $this->auth->hasIdentity()) {
            if ('POST' === strtoupper($request->getMethod())) {
                return $this->initiateAuthentication($request);
            }
            return null;
        }
        /** @var UserInterface */
        return ($this->userFactory)($this->auth->getIdentity());
    }

    /**
     * @psalm-suppress MixedMethodCall
     */
    public function unauthorizedResponse(ServerRequestInterface $request) : ResponseInterface
    {
        /** @var ResponseInterface */
        return ($this->responseFactory)()
            ->withHeader(
                'Location',
                $this->config['redirect']
            )
            ->withStatus(301);
    }

    /**
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UndefinedInterfaceMethod
     */
    private function initiateAuthentication(ServerRequestInterface $request) : ?UserInterface
    {
        $params = $request->getParsedBody();
        /** @var string */
        $username = $this->config['username'] ?? 'username';
        /** @var string */
        $password = $this->config['password'] ?? 'password';

        if (! isset($params[$username]) || ! isset($params[$password])) {
            return null;
        }

        $this->auth->getAdapter()->setIdentity($params[$username]);
        $this->auth->getAdapter()->setCredential($params[$password]);

        $result = $this->auth->authenticate();
        if (! $result->isValid()) {
            return null;
        }

        /** @var UserInterface*/
        return ($this->userFactory)($result->getIdentity());
    }
}
