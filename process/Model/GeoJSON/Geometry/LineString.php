<?php

namespace App\Model\GeoJSON\Geometry;

class LineString extends Geometry
{
    public string $type = 'LineString';

    /** @var array<array{number,number}> */
    public array $coordinates;

    /**
     * @param array<array{number,number}> $coordinates
     */
    public function __construct(array $coordinates)
    {
        parent::__construct($coordinates);
    }
}
