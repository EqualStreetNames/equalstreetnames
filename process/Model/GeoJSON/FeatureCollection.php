<?php

namespace App\Model\GeoJSON;

use JsonSerializable;

class FeatureCollection implements JsonSerializable
{
    public string $type = 'FeatureCollection';
  /** @var Feature[] $features */
    public array $features = [];

    public function jsonSerialize() {
      return [
        'type' => $this->type,
        'features' => $this->features
      ];
    }
}
