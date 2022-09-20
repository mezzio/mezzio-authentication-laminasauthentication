<?php

declare(strict_types=1);

namespace Mezzio\Authentication\LaminasAuthentication;

use Laminas\Authentication\AuthenticationService;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\LaminasAuthentication\Response\CallableResponseFactoryDecorator;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function is_callable;
use function strtoupper;

class LaminasAuthentication implements AuthenticationInterface
{
    /** @var AuthenticationService */
    protected $auth;

    /** @var array */
    protected $config;

    /** @var ResponseFactoryInterface */
    protected $responseFactory;

    /** @var callable */
    protected $userFactory;

    // phpcs:disable SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.InvalidFormat
    /** @param (callable():ResponseInterface)|ResponseFactoryInterface $responseFactory */
    public function __construct(
        AuthenticationService $auth,
        array $config,
        $responseFactory,
        callable $userFactory
    ) {
        $this->auth   = $auth;
        $this->config = $config;

        // Ensures type safety of the composed factory
        if (is_callable($responseFactory)) {
            // Ensures type safety of the composed factory
            $responseFactory = new CallableResponseFactoryDecorator(
                static fn(): ResponseInterface => $responseFactory()
            );
        }

        $this->responseFactory = $responseFactory;

        // Ensures type safety of the composed factory
        $this->userFactory = static fn(string $identity, array $roles = [], array $details = []): UserInterface
                => $userFactory($identity, $roles, $details);
    }

    public function authenticate(ServerRequestInterface $request): ?UserInterface
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

    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(301)
            ->withHeader(
                'Location',
                $this->config['redirect']
            );
    }

    private function initiateAuthentication(ServerRequestInterface $request): ?UserInterface
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
