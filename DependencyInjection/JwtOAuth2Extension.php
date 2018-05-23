<?php

namespace JwtOAuth2Bundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class JwtOAuth2Extension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter(
            'jwt_o_auth2.access_token_repository.class',
            $config['access_token_repository']['class']
        );
        $container->setParameter(
            'jwt_o_auth2.public_key.file',
            $config['public_key']['file']
        );
    }
}
