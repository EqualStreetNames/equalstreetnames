<?php

namespace App\Model\GeoJSON;

class Geometry
{
  public string $type;

  /** @var number[]|number[][] */
  public array $coordinates;

  /**
   * @param number[]|number[][] $coordinates
   */
  public function __construct(array $coordinates)
  {
    $this->coordinates = $coordinates;
  }
}
