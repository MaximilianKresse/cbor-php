<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace CBOR;

use InvalidArgumentException;

class ListObject extends AbstractCBORObject implements \Countable, \IteratorAggregate
{
    private const MAJOR_TYPE = 0b100;

    /**
     * @var CBORObject[]
     */
    private $data = [];

    /**
     * @var int|null
     */
    private $length;

    /**
     * @param CBORObject[] $data
     */
    public function __construct(array $data = [])
    {
        list($additionalInformation, $length) = LengthCalculator::getLengthOfArray($data);
        array_map(function ($item) {
            if (!$item instanceof CBORObject) {
                throw new InvalidArgumentException('The list must contain only CBORObject objects.');
            }
        }, $data);

        parent::__construct(self::MAJOR_TYPE, $additionalInformation);
        $this->data = $data;
        $this->length = $length;
    }

    public function add(CBORObject $object): void
    {
        $this->data[] = $object;
        list($this->additionalInformation, $this->length) = LengthCalculator::getLengthOfArray($this->data);
    }

    public function get(int $index): CBORObject
    {
        if (!\array_key_exists($index, $this->data)) {
            throw new InvalidArgumentException('Index not found.');
        }

        return $this->data[$index];
    }

    public function getNormalizedData(bool $ignoreTags = false): array
    {
        return array_map(function (CBORObject $item) use ($ignoreTags) {
            return $item->getNormalizedData($ignoreTags);
        }, $this->data);
    }

    public function count(): int
    {
        return \count($this->data);
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->data);
    }

    public function __toString(): string
    {
        $result = parent::__toString();
        if (null !== $this->length) {
            $result .= $this->length;
        }
        foreach ($this->data as $object) {
            $result .= (string) $object;
        }

        return $result;
    }
}
