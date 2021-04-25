<?php

namespace App\Model\Overpass;

class Overpass {
  public string $version;
  public string $generator;
  public object $osm3s;
  /** @var Element[] */
  public array $elements;
}
