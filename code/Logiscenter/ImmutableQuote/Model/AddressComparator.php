<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Magento\Quote\Api\Data\AddressInterface;

/**
 * Compares checkout address payloads so idempotent re-submits
 */
class AddressComparator
{
    /**
     * Constant array of getter methods used for comparing address fields
     * @var array<string>
     */
    private const COMPARABLE_GETTERS = [
        'getFirstname',
        'getLastname',
        'getCompany',
        'getStreet',
        'getCity',
        'getRegion',
        'getRegionId',
        'getPostcode',
        'getCountryId',
        'getTelephone',
    ];

    /**
     * Compares two address objects to determine if they represent the same address
     *
     * @param AddressInterface|null $incoming
     * @param AddressInterface|null $current
     * @return boolean
     */
    public function isSameAddress(?AddressInterface $incoming, ?AddressInterface $current): bool
    {
        if ($incoming === null || $current === null) {
            return $incoming === $current;
        }
        foreach (self::COMPARABLE_GETTERS as $getter) {
            if ($this->normalize($incoming->$getter()) !== $this->normalize($current->$getter())) {
                return false;
            }
        }
        return true;
    }

    /**
     * Compares the current shipping method with the specified carrier and method codes
     *
     * @param string|null $current
     * @param string $carrierCode
     * @param string $methodCode
     * @return boolean
     */
    public function isSameShippingMethod(?string $current, string $carrierCode, string $methodCode): bool
    {
        return $current === $carrierCode . '_' . $methodCode;
    }

    /**
     * Normalizes a value for comparison by converting it to a string and trimming whitespace
     *
     * @param mixed $value
     * @return string
     */
    private function normalize(mixed $value): string
    {
        if (is_array($value)) {
            return implode('|', array_map('strval', $value));
        }
        return trim((string)$value);
    }
}
