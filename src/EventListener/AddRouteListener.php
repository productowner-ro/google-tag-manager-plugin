<?php

declare(strict_types=1);

namespace GtmPlugin\EventListener;

use GtmPlugin\Resolver\ChannelFeatureResolver;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Xynnn\GoogleTagManagerBundle\Service\GoogleTagManagerInterface;

final class AddRouteListener
{
    private GoogleTagManagerInterface $googleTagManager;

    private ?ChannelFeatureResolver $featureResolver;

    public function __construct(
        GoogleTagManagerInterface $googleTagManager,
        ?ChannelFeatureResolver $featureResolver = null,
    ) {
        $this->googleTagManager = $googleTagManager;
        $this->featureResolver = $featureResolver;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $isMain = method_exists($event, 'isMainRequest')
            ? $event->isMainRequest()
            : $event->isMasterRequest();
        if (!$isMain) {
            return;
        }

        if ($this->featureResolver !== null && !$this->featureResolver->isEnabled('route')) {
            return;
        }

        $this->googleTagManager->setData('route', $event->getRequest()->get('_route'));
    }
}
