<?php

namespace Mollie\Application\Command;

use Mollie\DTO\ApplePay\Product;

final class UpdateApplePayShippingContact
{
    /**
     * @var Product[]
     */
    private $products;
    /**
     * @var int
     */
    private $cartId;
    /**
     * @var string
     */
    private $postalCode;
    /**
     * @var string
     */
    private $countryCode;
    /**
     * @var string
     */
    private $country;
    /**
     * @var string
     */
    private $locality;
    /**
     * @var int
     */
    private $customerId;

    public function __construct(
        array $products,
        int $cartId,
        string $postalCode,
        string $countryCode,
        string $country,
        string $locality,
        int $customerId
    ) {
        $this->products = $products;
        $this->cartId = $cartId;
        $this->postalCode = $postalCode;
        $this->countryCode = $countryCode;
        $this->country = $country;
        $this->locality = $locality;
        $this->customerId = $customerId;
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function getCartId(): int
    {
        return $this->cartId;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getLocality(): string
    {
        return $this->locality;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }
}
