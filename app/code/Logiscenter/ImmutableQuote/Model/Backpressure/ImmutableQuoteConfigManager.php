<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model\Backpressure;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfig;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfigManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ImmutableQuoteConfigManager implements LimitConfigManagerInterface
{
    const REQUEST_TYPE_ID = 'immutable-quote-activation';

    const XML_PATH_APPLY_RATE_LIMITING = 'immutable_quote/backpressure/enabled';
    const XML_PATH_REQUESTS_LIMIT = 'immutable_quote/backpressure/limit';
    const XML_PATH_REQUESTS_PERIOD = 'immutable_quote/backpressure/period';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {

    }

    /**
     * @inheritDoc
     */
    public function readLimit(ContextInterface $context): LimitConfig
    {
        return new LimitConfig($this->getRequestsLimit(), $this->getPeriod());
    }

    /**
     * @return bool
     */
    public function applyRateLimiting(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_APPLY_RATE_LIMITING, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function getRequestsLimit(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_REQUESTS_LIMIT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function getPeriod(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_REQUESTS_PERIOD, ScopeInterface::SCOPE_STORE);
    }
}
