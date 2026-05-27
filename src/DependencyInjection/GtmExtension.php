<?php

declare(strict_types=1);

namespace GtmPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class GtmExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        if ($config['inject'] === true) {
            $loader->load('inject.yml');
        }

        foreach ($config['features'] as $feature => $setting) {
            $parameter = \sprintf('gtm.features.%s', $feature);

            $container->setParameter($parameter, $setting);

            if ($setting === true) {
                $loader->load(\sprintf('features/%s.yml', $feature));
            }
        }

        $container->setParameter('gtm.features', $config['features']);
        $container->setParameter('gtm.channels', $this->normaliseChannels($config['channels'] ?? []));

        $loader->load('channels.yml');
    }

    /**
     * @param array<string, array{id?: ?string, enabled?: ?bool, features?: array<string, bool>}> $channels
     *
     * @return array<string, array{id: ?string, enabled: bool, features: array<string, bool>}>
     */
    private function normaliseChannels(array $channels): array
    {
        $normalised = [];
        foreach ($channels as $code => $entry) {
            $id = $entry['id'] ?? null;
            $enabled = $entry['enabled'] ?? ($id !== null);
            $features = $entry['features'] ?? [];
            $normalised[$code] = ['id' => $id, 'enabled' => $enabled, 'features' => $features];
        }

        return $normalised;
    }
}
