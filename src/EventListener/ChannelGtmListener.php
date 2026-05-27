<?php

declare(strict_types=1);

namespace GtmPlugin\EventListener;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Xynnn\GoogleTagManagerBundle\Service\GoogleTagManagerInterface;

final class ChannelGtmListener
{
    private GoogleTagManagerInterface $googleTagManager;

    private ChannelContextInterface $channelContext;

    /** @var array<string, array{id: ?string, enabled: bool}> */
    private array $channels;

    /**
     * @param array<string, array{id: ?string, enabled: bool}> $channels Pre-normalised by GtmExtension::load().
     */
    public function __construct(
        GoogleTagManagerInterface $googleTagManager,
        ChannelContextInterface $channelContext,
        array $channels,
    ) {
        $this->googleTagManager = $googleTagManager;
        $this->channelContext = $channelContext;
        $this->channels = $channels;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $isMain = method_exists($event, 'isMainRequest')
            ? $event->isMainRequest()
            : $event->isMasterRequest();
        if (!$isMain) {
            return;
        }

        try {
            $code = $this->channelContext->getChannel()->getCode();
        } catch (ChannelNotFoundException $e) {
            return;
        }

        $config = $this->channels[$code] ?? null;
        if ($config === null) {
            return;
        }

        if ($config['id'] !== null) {
            $this->googleTagManager->setId($config['id']);
        }
        $config['enabled'] ? $this->googleTagManager->enable() : $this->googleTagManager->disable();
    }
}
