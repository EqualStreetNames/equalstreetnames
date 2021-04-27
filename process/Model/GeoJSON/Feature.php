<?php

namespace App\Model\GeoJSON;

use App\Model\GeoJSON\Geometry\Geometry;

class Feature
{
    public string $type = 'Feature';
    public int $id;
    public Properties $properties;
    public ?Geometry $geometry;
}
