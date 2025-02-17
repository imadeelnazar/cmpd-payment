<?php

declare(strict_types=1);

/*
 * PaypalServerSdkLib
 *
 * This file was automatically generated by APIMATIC v3.0 ( https://www.apimatic.io ).
 */

namespace PaypalServerSdkLib\Models;

use stdClass;

/**
 * Details shared by Google for the merchant to be shared with PayPal. This is required to process the
 * transaction using the Google Pay payment method.
 */
class GooglePayDecryptedTokenData implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $messageId;

    /**
     * @var string|null
     */
    private $messageExpiration;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var string
     */
    private $authenticationMethod;

    /**
     * @var string|null
     */
    private $cryptogram;

    /**
     * @var string|null
     */
    private $eciIndicator;

    /**
     * @param string $paymentMethod
     * @param string $authenticationMethod
     */
    public function __construct(string $paymentMethod, string $authenticationMethod)
    {
        $this->paymentMethod = $paymentMethod;
        $this->authenticationMethod = $authenticationMethod;
    }

    /**
     * Returns Message Id.
     * A unique ID that identifies the message in case it needs to be revoked or located at a later time.
     */
    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    /**
     * Sets Message Id.
     * A unique ID that identifies the message in case it needs to be revoked or located at a later time.
     *
     * @maps message_id
     */
    public function setMessageId(?string $messageId): void
    {
        $this->messageId = $messageId;
    }

    /**
     * Returns Message Expiration.
     * Date and time at which the message expires as UTC milliseconds since epoch. Integrators should
     * reject any message that's expired.
     */
    public function getMessageExpiration(): ?string
    {
        return $this->messageExpiration;
    }

    /**
     * Sets Message Expiration.
     * Date and time at which the message expires as UTC milliseconds since epoch. Integrators should
     * reject any message that's expired.
     *
     * @maps message_expiration
     */
    public function setMessageExpiration(?string $messageExpiration): void
    {
        $this->messageExpiration = $messageExpiration;
    }

    /**
     * Returns Payment Method.
     * The type of the payment credential. Currently, only CARD is supported.
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * Sets Payment Method.
     * The type of the payment credential. Currently, only CARD is supported.
     *
     * @required
     * @maps payment_method
     */
    public function setPaymentMethod(string $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Returns Authentication Method.
     * Authentication Method which is used for the card transaction.
     */
    public function getAuthenticationMethod(): string
    {
        return $this->authenticationMethod;
    }

    /**
     * Sets Authentication Method.
     * Authentication Method which is used for the card transaction.
     *
     * @required
     * @maps authentication_method
     */
    public function setAuthenticationMethod(string $authenticationMethod): void
    {
        $this->authenticationMethod = $authenticationMethod;
    }

    /**
     * Returns Cryptogram.
     * Base-64 cryptographic identifier used by card schemes to validate the token verification result.
     * This is a conditionally required field if authentication_method is CRYPTOGRAM_3DS.
     */
    public function getCryptogram(): ?string
    {
        return $this->cryptogram;
    }

    /**
     * Sets Cryptogram.
     * Base-64 cryptographic identifier used by card schemes to validate the token verification result.
     * This is a conditionally required field if authentication_method is CRYPTOGRAM_3DS.
     *
     * @maps cryptogram
     */
    public function setCryptogram(?string $cryptogram): void
    {
        $this->cryptogram = $cryptogram;
    }

    /**
     * Returns Eci Indicator.
     * Electronic Commerce Indicator may not always be present. It is only returned for tokens on the Visa
     * card network. This value is passed through in the payment authorization request.
     */
    public function getEciIndicator(): ?string
    {
        return $this->eciIndicator;
    }

    /**
     * Sets Eci Indicator.
     * Electronic Commerce Indicator may not always be present. It is only returned for tokens on the Visa
     * card network. This value is passed through in the payment authorization request.
     *
     * @maps eci_indicator
     */
    public function setEciIndicator(?string $eciIndicator): void
    {
        $this->eciIndicator = $eciIndicator;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        if (isset($this->messageId)) {
            $json['message_id']         = $this->messageId;
        }
        if (isset($this->messageExpiration)) {
            $json['message_expiration'] = $this->messageExpiration;
        }
        $json['payment_method']         = GooglePayPaymentMethod::checkValue($this->paymentMethod);
        $json['authentication_method']  = GooglePayAuthenticationMethod::checkValue($this->authenticationMethod);
        if (isset($this->cryptogram)) {
            $json['cryptogram']         = $this->cryptogram;
        }
        if (isset($this->eciIndicator)) {
            $json['eci_indicator']      = $this->eciIndicator;
        }

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
