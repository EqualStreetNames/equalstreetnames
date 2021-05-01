<?php

namespace App\Model\GeoJSON\Geometry;

class MultiLineString extends Geometry
{
    public string $type = 'MultiLineString';

    /** @var array<array<array{number,number}>> */
    public array $coordinates;

    /**
     * @param array<array<array{number,number}>> $coordinates
     */
    public function __construct(array $coordinates)
    {
        parent::__construct($coordinates);
    }
}
