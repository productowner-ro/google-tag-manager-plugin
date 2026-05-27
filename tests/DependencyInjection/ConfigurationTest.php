<?php

declare(strict_types=1);

namespace Tests\GtmPlugin\DependencyInjection;

use GtmPlugin\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    public function testChannelsNodeAcceptsPerChannelOverrides(): void
    {
        $this->assertProcessedConfigurationEquals(
            [
                [
                    'channels' => [
                        'us_web' => [
                            'id' => 'GTM-USA',
                            'enabled' => true,
                            'features' => ['events' => false],
                        ],
                        'eu_web' => [
                            'enabled' => false,
                        ],
                    ],
                ],
            ],
            [
                'inject' => true,
                'features' => [
                    'environment' => true,
                    'route' => true,
                    'context' => true,
                    'events' => true,
                ],
                'channels' => [
                    'us_web' => [
                        'id' => 'GTM-USA',
                        'enabled' => true,
                        'features' => ['events' => false],
                    ],
                    'eu_web' => [
                        'id' => null,
                        'enabled' => false,
                    ],
                ],
            ],
        );
    }

    public function testChannelsNodeDefaultsToEmpty(): void
    {
        $this->assertProcessedConfigurationEquals(
            [[]],
            [
                'inject' => true,
                'features' => [
                    'environment' => true,
                    'route' => true,
                    'context' => true,
                    'events' => true,
                ],
                'channels' => [],
            ],
        );
    }

    public function testEmptyChannelEntryIsInvalid(): void
    {
        $this->assertPartialConfigurationIsInvalid(
            [
                [
                    'channels' => [
                        'us_web' => [],
                    ],
                ],
            ],
            'channels',
        );
    }
}
