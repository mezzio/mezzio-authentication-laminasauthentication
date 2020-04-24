<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-laminasauthentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-laminasauthentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-laminasauthentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication\LaminasAuthentication;

use Laminas\Authentication\AuthenticationService;
use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

use function sprintf;

class LaminasAuthenticationFactory
{
    public function __invoke(ContainerInterface $container) : LaminasAuthentication
    {
        $auth = $container->has(AuthenticationService::class)
            ? $container->get(AuthenticationService::class)
            : ($container->has(\Zend\Authentication\AuthenticationService::class)
                ? $container->get(\Zend\Authentication\AuthenticationService::class)
                : null);

        if (null === $auth) {
            throw new Exception\InvalidConfigException(sprintf(
                'The %s service is missing',
                AuthenticationService::class
            ));
        }

        $config = $container->get('config')['authentication'] ?? [];

        if (! isset($config['redirect'])) {
            throw new Exception\InvalidConfigException(
                'The redirect URL is missing for authentication'
            );
        }

        if (! $container->has(UserInterface::class)
            && ! $container->has(\Zend\Expressive\Authentication\UserInterface::class)
        ) {
            throw new Exception\InvalidConfigException(
                'UserInterface factory service is missing for authentication'
            );
        }

        return new LaminasAuthentication(
            $auth,
            $config,
            $container->get(ResponseInterface::class),
            $container->has(UserInterface::class)
                ? $container->get(UserInterface::class)
                : $container->get(\Zend\Expressive\Authentication\UserInterface::class)
        );
    }
}
