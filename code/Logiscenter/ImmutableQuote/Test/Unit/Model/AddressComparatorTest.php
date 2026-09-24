<?php
declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Model;

use Logiscenter\ImmutableQuote\Model\AddressComparator;
use Magento\Quote\Api\Data\AddressInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressComparatorTest extends TestCase
{
    private AddressComparator $comparator;

    protected function setUp(): void
    {
        $this->comparator = new AddressComparator();
    }

    public function testBothNullAddressesAreSame(): void
    {
        self::assertTrue($this->comparator->isSameAddress(null, null));
    }

    public function testOneNullAddressIsNotSame(): void
    {
        self::assertFalse($this->comparator->isSameAddress($this->createAddress(), null));
        self::assertFalse($this->comparator->isSameAddress(null, $this->createAddress()));
    }

    public function testIdenticalAddressesAreSame(): void
    {
        $incoming = $this->createAddress(street: ['123 Main St']);
        $current = $this->createAddress(street: ['123 Main St']);
        self::assertTrue($this->comparator->isSameAddress($incoming, $current));
    }

    public function testDifferingFieldMakesAddressesNotSame(): void
    {
        $incoming = $this->createAddress(city: 'Springfield');
        $current = $this->createAddress(city: 'Shelbyville');
        self::assertFalse($this->comparator->isSameAddress($incoming, $current));
    }

    public function testValuesAreTrimmedBeforeComparing(): void
    {
        $incoming = $this->createAddress(company: ' Acme ');
        $current = $this->createAddress(company: 'Acme');
        self::assertTrue($this->comparator->isSameAddress($incoming, $current));
    }

    public function testSameShippingMethodMatchesCarrierAndMethodCode(): void
    {
        self::assertTrue($this->comparator->isSameShippingMethod('flatrate_flatrate', 'flatrate', 'flatrate'));
        self::assertFalse($this->comparator->isSameShippingMethod('flatrate_flatrate', 'tablerate', 'bestway'));
        self::assertFalse($this->comparator->isSameShippingMethod(null, 'flatrate', 'flatrate'));
    }

    private function createAddress(
        string $firstname = 'John',
        string $lastname = 'Doe',
        string $company = 'Acme',
        array $street = ['123 Main St'],
        string $city = 'Springfield',
        string $region = 'IL',
        int $regionId = 1,
        string $postcode = '62701',
        string $countryId = 'US',
        string $telephone = '555-1234'
    ): AddressInterface&MockObject {
        $address = $this->createMock(AddressInterface::class);
        $address->method('getFirstname')->willReturn($firstname);
        $address->method('getLastname')->willReturn($lastname);
        $address->method('getCompany')->willReturn($company);
        $address->method('getStreet')->willReturn($street);
        $address->method('getCity')->willReturn($city);
        $address->method('getRegion')->willReturn($region);
        $address->method('getRegionId')->willReturn($regionId);
        $address->method('getPostcode')->willReturn($postcode);
        $address->method('getCountryId')->willReturn($countryId);
        $address->method('getTelephone')->willReturn($telephone);
        return $address;
    }
}
