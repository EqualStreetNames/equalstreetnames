<?php

namespace App\Model\GeoJSON;

class MultiLineString extends Geometry
{
  public string $type = 'MultiLineString';

  /**
   * @param number[][] $coordinates
   */
  public function __construct(array $coordinates)
  {
    parent::__construct($coordinates);
  }
}
