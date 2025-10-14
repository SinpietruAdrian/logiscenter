<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Plugin;

use Logiscenter\ImmutableQuote\Model\ImmutableQuote\Validator;
use Magento\Quote\Model\Quote;

class PreventMerge
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function aroundMerge(Quote $subject, \Closure $proceed, Quote $quote): Quote
    {
        if ($this->validator->isImmutableQuote($subject)) {
            return $subject;
        }

        return $proceed($quote);
    }
}
