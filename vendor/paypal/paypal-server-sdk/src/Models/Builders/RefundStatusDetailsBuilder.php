<?php

declare(strict_types=1);

/*
 * PaypalServerSdkLib
 *
 * This file was automatically generated by APIMATIC v3.0 ( https://www.apimatic.io ).
 */

namespace PaypalServerSdkLib\Models\Builders;

use Core\Utils\CoreHelper;
use PaypalServerSdkLib\Models\RefundStatusDetails;

/**
 * Builder for model RefundStatusDetails
 *
 * @see RefundStatusDetails
 */
class RefundStatusDetailsBuilder
{
    /**
     * @var RefundStatusDetails
     */
    private $instance;

    private function __construct(RefundStatusDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new refund status details Builder object.
     */
    public static function init(): self
    {
        return new self(new RefundStatusDetails());
    }

    /**
     * Sets reason field.
     */
    public function reason(?string $value): self
    {
        $this->instance->setReason($value);
        return $this;
    }

    /**
     * Initializes a new refund status details object.
     */
    public function build(): RefundStatusDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
