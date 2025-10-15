<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model\Backpressure;

use Logiscenter\ImmutableQuote\Api\ImmutableQuoteManagementInterface;
use Magento\Framework\Webapi\Backpressure\BackpressureRequestTypeExtractorInterface;

class WebapiRequestTypeExtractor implements BackpressureRequestTypeExtractorInterface
{
    public function __construct(private readonly ImmutableQuoteConfigManager $configManager)
    {
    }

    /**
     * @inheritDoc
     */
    public function extract(string $service, string $method, string $endpoint): ?string
    {
        if ($service == ImmutableQuoteManagementInterface::class && $this->configManager->applyRateLimiting()) {
            return ImmutableQuoteConfigManager::REQUEST_TYPE_ID;
        }

        return null;
    }
}
