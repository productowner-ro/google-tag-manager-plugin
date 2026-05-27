<?php

declare(strict_types=1);

namespace Tests\GtmPlugin\EventListener;

use GtmPlugin\EventListener\AddRouteListener;
use GtmPlugin\Resolver\ChannelFeatureResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Xynnn\GoogleTagManagerBundle\Service\GoogleTagManager;

final class AddRouteListenerTest extends TestCase
{
    public function testAddRouteIsAddedToGtmObject()
    {
        $request = new Request(['_route' => 'test_route']);

        $gtm = new GoogleTagManager(true, 'id1234');
        $listener = new AddRouteListener($gtm);

        $mock = $this->getMockBuilder(RequestEvent::class)->disableOriginalConstructor()->getMock();

        $mock
            ->expects($this->once())
            ->method('isMainRequest')
            ->willReturn(true)
        ;

        $mock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request)
        ;

        $listener->onKernelRequest($mock);

        $this->assertArrayHasKey('route', $gtm->getData());
        $this->assertSame($gtm->getData()['route'], 'test_route');
    }

    public function testSkipsWhenResolverDisablesRouteFeature(): void
    {
        $gtm = new GoogleTagManager(true, 'id1234');
        $resolver = $this->createMock(ChannelFeatureResolver::class);
        $resolver->method('isEnabled')->with('route')->willReturn(false);

        $listener = new AddRouteListener($gtm, $resolver);
        $event = $this->getMockBuilder(RequestEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('isMainRequest')->willReturn(true);
        $listener->onKernelRequest($event);

        $this->assertArrayNotHasKey('route', $gtm->getData());
    }
}
