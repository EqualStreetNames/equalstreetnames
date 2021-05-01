<?php

namespace App\Model\GeoJSON;

use App\Model\GeoJSON\Geometry\Geometry;
use JsonSerializable;

class Feature implements JsonSerializable
{
    public string $type = 'Feature';
    public int $id;
    public Properties $properties;
    public ?Geometry $geometry;

    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'properties' => $this->properties,
            'geometry' => $this->geometry,
        ];
    }
}
