<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Api;

/**
 * Configuration Repository Interface
 * @api
 */
interface ConfigRepositoryInterface
{
    /**
     * XML path to check the rate limit configuration
     * @var string
     */
    public const  XML_RATE_LIMIT = 'logiscenter_immutable_quote/security/rate_limit';

    /**
     * Get the rate limit configuration value
     *
     * @return int
     */
    public function getRateLimit(): int;
}
