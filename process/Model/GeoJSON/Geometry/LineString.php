<?php

namespace App\Model\GeoJSON\Geometry;

class LineString extends Geometry
{
    public string $type = 'LineString';

  /**
   * @param number[] $coordinates
   */
    public function __construct(array $coordinates)
    {
        parent::__construct($coordinates);
    }
}
