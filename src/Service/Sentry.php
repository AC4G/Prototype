<?php declare(strict_types=1);

namespace App\Service;

use Sentry\Tracing\SamplingContext;

class Sentry
{
    public function getTracesSampler(): callable
    {
        return function(SamplingContext $context): float {
            return 1.0;
        };
    }
}