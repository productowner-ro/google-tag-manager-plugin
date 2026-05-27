<?php

declare(strict_types=1);

namespace Tests\GtmPlugin\EventListener;

use GtmPlugin\EventListener\ChannelGtmListener;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Channel\Model\ChannelInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Xynnn\GoogleTagManagerBundle\Service\GoogleTagManagerInterface;

final class ChannelGtmListenerTest extends TestCase
{
    public function testSetsIdAndEnablesForMatchingChannel(): void
    {
        $gtm = $this->createMock(GoogleTagManagerInterface::class);
        $gtm->expects($this->once())->method('setId')->with('GTM-USA');
        $gtm->expects($this->once())->method('enable');
        $gtm->expects($this->never())->method('disable');

        $listener = new ChannelGtmListener(
            $gtm,
            $this->channelContextReturning('us_web'),
            ['us_web' => ['id' => 'GTM-USA', 'enabled' => true]],
        );

        $listener->onKernelRequest($this->mainRequestEvent());
    }

    public function testDisablesWhenChannelEntryDisables(): void
    {
        $gtm = $this->createMock(GoogleTagManagerInterface::class);
        $gtm->expects($this->never())->method('setId');
        $gtm->expects($this->never())->method('enable');
        $gtm->expects($this->once())->method('disable');

        $listener = new ChannelGtmListener(
            $gtm,
            $this->channelContextReturning('eu_web'),
            ['eu_web' => ['id' => null, 'enabled' => false]],
        );

        $listener->onKernelRequest($this->mainRequestEvent());
    }

    public function testDoesNothingForUnconfiguredChannel(): void
    {
        $gtm = $this->createMock(GoogleTagManagerInterface::class);
        $gtm->expects($this->never())->method('setId');
        $gtm->expects($this->never())->method('enable');
        $gtm->expects($this->never())->method('disable');

        $listener = new ChannelGtmListener(
            $gtm,
            $this->channelContextReturning('other'),
            ['us_web' => ['id' => 'GTM-USA', 'enabled' => true]],
        );

        $listener->onKernelRequest($this->mainRequestEvent());
    }

    public function testIgnoresChannelNotFound(): void
    {
        $gtm = $this->createMock(GoogleTagManagerInterface::class);
        $gtm->expects($this->never())->method('setId');

        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willThrowException(new ChannelNotFoundException());

        $listener = new ChannelGtmListener($gtm, $channelContext, ['us_web' => ['id' => 'GTM-USA', 'enabled' => true]]);
        $listener->onKernelRequest($this->mainRequestEvent());
    }

    public function testIgnoresSubRequests(): void
    {
        $gtm = $this->createMock(GoogleTagManagerInterface::class);
        $gtm->expects($this->never())->method('setId');

        $listener = new ChannelGtmListener(
            $gtm,
            $this->channelContextReturning('us_web'),
            ['us_web' => ['id' => 'GTM-USA', 'enabled' => true]],
        );

        $event = $this->createMock(RequestEvent::class);
        $event->method('isMainRequest')->willReturn(false);
        $listener->onKernelRequest($event);
    }

    private function channelContextReturning(string $code): ChannelContextInterface
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getCode')->willReturn($code);
        $ctx = $this->createMock(ChannelContextInterface::class);
        $ctx->method('getChannel')->willReturn($channel);

        return $ctx;
    }

    private function mainRequestEvent(): RequestEvent
    {
        $event = $this->createMock(RequestEvent::class);
        $event->method('isMainRequest')->willReturn(true);

        return $event;
    }
}
