<?php

namespace Cable8mm\Waybill\Enums;

enum ParcelService: string
{
    case Cj = 'CJ택배';

    public function factoryClass(): string
    {
        return match ($this) {
            self::Cj => \Cable8mm\Waybill\Factories\CjFactory::class,
        };
    }

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
