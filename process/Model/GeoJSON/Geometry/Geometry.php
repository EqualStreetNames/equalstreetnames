<?php

namespace App\Model\GeoJSON\Geometry;

class Geometry
{
    public string $type;

    /** @var array<array{number,number}>|array<array<array{number,number}>> */
    public array $coordinates;

    /**
     * @param array<array{number,number}>|array<array<array{number,number}>> $coordinates
     */
    public function __construct(array $coordinates)
    {
        $this->coordinates = $coordinates;
    }
}
