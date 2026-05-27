<?php

declare(strict_types=1);

namespace Tests\GtmPlugin\EventListener;

use GtmPlugin\EventListener\EnvironmentListener;
use GtmPlugin\Resolver\ChannelFeatureResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Xynnn\GoogleTagManagerBundle\Service\GoogleTagManager;

final class EnvironmentListenerTest extends TestCase
{
    public function testEnvironmentIsAddedToGtmObject(): void
    {
        $gtm = new GoogleTagManager(true, 'id1234');
        $listener = new EnvironmentListener($gtm, 'test_env');
        $mock = $this->getMockBuilder(RequestEvent::class)->disableOriginalConstructor()->getMock();
        $mock->method('isMainRequest')->willReturn(true);
        $listener->onKernelRequest($mock);

        $this->assertArrayHasKey('env', $gtm->getData());
        $this->assertSame($gtm->getData()['env'], 'test_env');
    }

    public function testSkipsWhenResolverDisablesEnvironmentFeature(): void
    {
        $gtm = new GoogleTagManager(true, 'id1234');
        $resolver = $this->createMock(ChannelFeatureResolver::class);
        $resolver->method('isEnabled')->with('environment')->willReturn(false);

        $listener = new EnvironmentListener($gtm, 'test_env', $resolver);
        $event = $this->getMockBuilder(RequestEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('isMainRequest')->willReturn(true);
        $listener->onKernelRequest($event);

        $this->assertArrayNotHasKey('env', $gtm->getData());
    }
}
