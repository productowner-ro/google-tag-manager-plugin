<?php

declare(strict_types=1);

namespace Tests\GtmPlugin\DependencyInjection;

use GtmPlugin\DependencyInjection\GtmExtension;
use GtmPlugin\EventListener\ChannelGtmListener;
use GtmPlugin\Resolver\ChannelFeatureResolver;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

final class GtmExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [new GtmExtension()];
    }

    public function testRegistersResolverAndChannelListener(): void
    {
        $this->load([
            'channels' => [
                'us_web' => ['id' => 'GTM-USA', 'enabled' => true],
            ],
        ]);

        $this->assertContainerBuilderHasService(ChannelFeatureResolver::class);
        $this->assertContainerBuilderHasService(ChannelGtmListener::class);
        $this->assertContainerBuilderHasParameter('gtm.channels');
    }

    public function testNormalisesChannelDefaultsEnabledFromIdPresence(): void
    {
        $this->load([
            'channels' => [
                'us_web' => ['id' => 'GTM-USA'],
                'eu_web' => ['enabled' => false],
            ],
        ]);

        $this->assertContainerBuilderHasParameter('gtm.channels', [
            'us_web' => ['id' => 'GTM-USA', 'enabled' => true, 'features' => []],
            'eu_web' => ['id' => null, 'enabled' => false, 'features' => []],
        ]);
    }
}
