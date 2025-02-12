<?php

namespace Cable8mm\Waybill\Enums;

enum ParcelService: string
{
    /**
     * Parcel Services
     */
    case Cj = 'CJ택배';

    /**
     * Get the name of the factory class with namespace
     *
     * @return string The name of the factory class with namespace
     *
     * @example ParcelService::Cj->factoryClass() Cable8mm\Waybill\Factories\CjFactory
     */
    public function factoryClass(): string
    {
        return match ($this) {
            self::Cj => \Cable8mm\Waybill\Factories\CjFactory::class,
        };
    }

    /**
     * Get the stub filename with path for the factory class
     *
     * @return string The stub filename with path for the factory class
     *
     * @example ParcelService::Cj->stub()   __DIR__/stubs/Cj.stub
     */
    public function stub(): string
    {
        $stubPath = match ($this) {
            self::Cj => realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'../stubs/'.$this->name.'.stub'),
        };

        if ($stubPath === false) {
            throw new \Exception('Failed to read file: no existing stub');
        }

        return $stubPath;
    }
}
