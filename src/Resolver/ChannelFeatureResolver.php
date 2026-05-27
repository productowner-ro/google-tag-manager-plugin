<?php

declare(strict_types=1);

namespace GtmPlugin\Resolver;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;

class ChannelFeatureResolver
{
    private ChannelContextInterface $channelContext;

    /** @var array<string, bool> */
    private array $globalFeatures;

    /** @var array<string, array{id?: ?string, enabled?: ?bool, features?: array<string, bool>}> */
    private array $channels;

    /**
     * @param array<string, bool> $globalFeatures
     * @param array<string, array{id?: ?string, enabled?: ?bool, features?: array<string, bool>}> $channels
     */
    public function __construct(ChannelContextInterface $channelContext, array $globalFeatures, array $channels)
    {
        $this->channelContext = $channelContext;
        $this->globalFeatures = $globalFeatures;
        $this->channels = $channels;
    }

    public function isEnabled(string $feature): bool
    {
        $global = $this->globalFeatures[$feature] ?? false;

        try {
            $code = $this->channelContext->getChannel()->getCode();
        } catch (ChannelNotFoundException $e) {
            return $global;
        }

        return $this->channels[$code]['features'][$feature] ?? $global;
    }
}
