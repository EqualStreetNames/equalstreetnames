<?php

namespace App\Model\GeoJSON;

class FeatureCollection
{
    public string $type = 'FeatureCollection';
  /** @var Feature[] $features */
    public array $features = [];
}
