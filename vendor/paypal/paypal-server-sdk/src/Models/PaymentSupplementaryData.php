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
 * The supplementary data.
 */
class PaymentSupplementaryData implements \JsonSerializable
{
    /**
     * @var RelatedIdentifiers|null
     */
    private $relatedIds;

    /**
     * Returns Related Ids.
     * Identifiers related to a specific resource.
     */
    public function getRelatedIds(): ?RelatedIdentifiers
    {
        return $this->relatedIds;
    }

    /**
     * Sets Related Ids.
     * Identifiers related to a specific resource.
     *
     * @maps related_ids
     */
    public function setRelatedIds(?RelatedIdentifiers $relatedIds): void
    {
        $this->relatedIds = $relatedIds;
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
        if (isset($this->relatedIds)) {
            $json['related_ids'] = $this->relatedIds;
        }

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
