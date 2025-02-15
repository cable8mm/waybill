<?php

namespace Cable8mm\Waybill\Collections;

use ArrayAccess;
use ArrayIterator;
use Cable8mm\Waybill\Waybill;
use Countable;
use IteratorAggregate;
use Traversable;

class Waybills implements ArrayAccess, Countable, IteratorAggregate
{
    public function __construct(
        /**
         * @var array<WayBill> Array of Waybill objects
         */
        private array $container = []
    ) {
        //
    }

    public function add(Waybill $waybill): static
    {
        $this->container[] = $waybill;

        return $this;
    }

    public function write(): void
    {
        foreach ($this->container as $waybill) {
            $waybill->write();
        }
    }

    public function count(): int
    {
        return count($this->container);
    }

    public function toArray(int $count = 1): array
    {
        $array = [];

        for ($i = 0; $i < $count; $i++) {
            foreach ($this->container as $waybill) {
                $array[] = $waybill->toArray();
            }
        }

        return $array;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this);
    }

    public static function make(?array $waybills = []): static
    {
        return new static($waybills);
    }
}
