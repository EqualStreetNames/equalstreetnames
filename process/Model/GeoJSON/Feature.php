<?php

namespace App\Model\GeoJSON;

class Feature
{
  public string $type = 'Feature';
  public mixed $id;
  public Properties $properties;
  public ?Geometry $geometry;
}
