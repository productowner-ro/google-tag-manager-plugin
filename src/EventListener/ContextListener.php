<?php

declare(strict_types=1);

namespace GtmPlugin\EventListener;

use GtmPlugin\Resolver\ChannelFeatureResolver;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Xynnn\GoogleTagManagerBundle\Service\GoogleTagManagerInterface;

final class ContextListener
{
    private GoogleTagManagerInterface $googleTagManager;

    private ChannelContextInterface $channelContext;

    private LocaleContextInterface $localeContext;

    private CurrencyContextInterface $currencyContext;

    private ?ChannelFeatureResolver $featureResolver;

    public function __construct(
        GoogleTagManagerInterface $googleTagManager,
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext,
        CurrencyContextInterface $currencyContext,
        ?ChannelFeatureResolver $featureResolver = null,
    ) {
        $this->googleTagManager = $googleTagManager;
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
        $this->currencyContext = $currencyContext;
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

        if ($this->featureResolver !== null && !$this->featureResolver->isEnabled('context')) {
            return;
        }

        try {
            $channel = $this->channelContext->getChannel();

            $this->googleTagManager->setData('channel', [
                'code' => $channel->getCode(),
                'name' => $channel->getName(),
            ]);

            $this->googleTagManager->setData('locale', $this->localeContext->getLocaleCode());

            $this->googleTagManager->setData('currency', $this->currencyContext->getCurrencyCode());
        } catch (ChannelNotFoundException $e) {
        } // Channel not found, nothing should happen in here
    }
}
