<?php

declare(strict_types=1);

namespace Tests\GtmPlugin\Twig;

use GtmPlugin\Resolver\ChannelFeatureResolver;
use GtmPlugin\Twig\GtmChannelExtension;
use PHPUnit\Framework\TestCase;

final class GtmChannelExtensionTest extends TestCase
{
    public function testRegistersGtmChannelAllowsFunction(): void
    {
        $extension = new GtmChannelExtension($this->createMock(ChannelFeatureResolver::class));

        $names = array_map(static fn ($fn) => $fn->getName(), $extension->getFunctions());

        $this->assertContains('gtm_channel_allows', $names);
    }

    public function testDelegatesToResolver(): void
    {
        $resolver = $this->createMock(ChannelFeatureResolver::class);
        $resolver->method('isEnabled')->with('events')->willReturn(false);

        $extension = new GtmChannelExtension($resolver);

        $this->assertFalse($extension->gtmChannelAllows('events'));
    }
}
