<?php

declare(strict_types=1);

namespace Mezzio\Authentication\LaminasAuthentication;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'authentication' => $this->getAuthenticationConfig(),
            'dependencies'   => $this->getDependencies(),
        ];
    }

    public function getAuthenticationConfig(): array
    {
        return [
            'redirect' => '', // URL to which to redirect for invalid credentials
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                LaminasAuthentication::class => LaminasAuthenticationFactory::class,
            ],
        ];
    }
}
