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
        if (method_exists($event, 'isMainRequest')) {
            if (!$event->isMainRequest()) {
                return;
            }
        }

        if (method_exists($event, 'isMasterRequest')) {
            if (!$event->isMasterRequest()) {
                return;
            }
        }

        if ($this->featureResolver !== null && !$this->featureResolver->isEnabled('route')) {
            return;
        }

        $this->googleTagManager->setData('route', $event->getRequest()->get('_route'));
    }
}
