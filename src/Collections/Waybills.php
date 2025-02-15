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
    /**
     * Constructs a new instance
     *
     * @param  array  $container  The container
     */
    public function __construct(
        /**
         * @var array<WayBill> Array of Waybill objects
         */
        private array $container = []
    ) {
        //
    }

    /**
     * Adds a Waybill object to the container
     *
     * @param  \Cable8mm\Waybill\Waybill|array  $waybill  a Waybill object
     * @return static The method returns the instance
     */
    public function add(Waybill|array $waybill): static
    {
        if (is_array($waybill)) {
            $this->container = array_merge($this->container, $waybill);
        } else {
            $this->container[] = $waybill;
        }

        return $this;
    }

    /**
     * Run `write()` method on the container
     *
     * @return static The method returns the instance
     */
    public function write(): static
    {
        foreach ($this->container as $waybill) {
            $waybill->write();
        }

        return $this;
    }

    /**
     * Get the count of container elements
     *
     * @return int The method returns the count of container elements
     */
    public function count(): int
    {
        return count($this->container);
    }

    /**
     * Get the iterator for the container
     *
     * @param  int  $count  The count of write operations
     * @return array The method returns combining with all waybill's array
     */
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

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): mixed
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this);
    }

    /**
     * Instance factory method
     *
     * @param  array|null  $waybills  An array of waybills
     * @return static The method returns a new instance
     */
    public static function make(?array $waybills = []): static
    {
        return new static($waybills);
    }
}
