<?php

declare(strict_types=1);

namespace GtmPlugin\EventListener;

use GtmPlugin\Resolver\ChannelFeatureResolver;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Xynnn\GoogleTagManagerBundle\Service\GoogleTagManagerInterface;

final class EnvironmentListener
{
    private GoogleTagManagerInterface $googleTagManager;

    private string $environment;

    private ?ChannelFeatureResolver $featureResolver;

    public function __construct(
        GoogleTagManagerInterface $googleTagManager,
        string $environment,
        ?ChannelFeatureResolver $featureResolver = null,
    ) {
        $this->googleTagManager = $googleTagManager;
        $this->environment = $environment;
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

        if ($this->featureResolver !== null && !$this->featureResolver->isEnabled('environment')) {
            return;
        }

        $this->googleTagManager->setData('env', $this->environment);
    }
}
