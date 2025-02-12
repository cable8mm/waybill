<?php

namespace Cable8mm\Waybill\Factories;

abstract class Factory
{
    /**
     * Change key-value pairs in the definition
     */
    private array $state;

    /**
     * Define a factory definition for online mall companies
     *
     * @return array The method returns a row of a specific order sheet for a company
     */
    abstract public function definition(): array;

    /**
     * Create a new Factory instance
     *
     * @param  ?array  $state  The new state
     * @return static The method returns a new Factory instance
     */
    public static function make(?array $state = []): static
    {
        return (new static)->state($state);
    }

    /**
     * Update the state of the returned definitions
     *
     * @param  array  $state  The new state
     * @return static The method returns the new state
     */
    public function state(array $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Create definition(s) with the given state and count
     *
     * @return array The method returns the definition with the given state and count
     */
    public function create(): array
    {
        $record = array_values($this->definition());

        if (! empty($this->state)) {
            foreach ($this->state as $key => $value) {
                $record[$key] = $this->{$key} ?? $value;
            }
        }

        return $record;
    }
}
