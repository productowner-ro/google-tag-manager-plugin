<?php

declare(strict_types=1);

namespace Tests\GtmPlugin\Resolver;

use GtmPlugin\Resolver\ChannelFeatureResolver;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Channel\Model\ChannelInterface;

final class ChannelFeatureResolverTest extends TestCase
{
    public function testFallsBackToGlobalWhenChannelHasNoOverride(): void
    {
        $resolver = $this->makeResolver('us_web', ['events' => true], []);

        $this->assertTrue($resolver->isEnabled('events'));
    }

    public function testPerChannelOverrideTakesPrecedence(): void
    {
        $resolver = $this->makeResolver('us_web', ['events' => true], [
            'us_web' => ['features' => ['events' => false]],
        ]);

        $this->assertFalse($resolver->isEnabled('events'));
    }

    public function testReturnsGlobalWhenChannelContextThrows(): void
    {
        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willThrowException(new ChannelNotFoundException());

        $resolver = new ChannelFeatureResolver($channelContext, ['events' => true], []);

        $this->assertTrue($resolver->isEnabled('events'));
    }

    /**
     * @param array<string, bool> $global
     * @param array<string, array{features?: array<string, bool>}> $channels
     */
    private function makeResolver(string $code, array $global, array $channels): ChannelFeatureResolver
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getCode')->willReturn($code);
        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willReturn($channel);

        return new ChannelFeatureResolver($channelContext, $global, $channels);
    }
}
