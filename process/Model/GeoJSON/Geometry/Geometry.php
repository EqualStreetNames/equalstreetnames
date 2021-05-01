<?php

namespace App\Model\GeoJSON\Geometry;

use JsonSerializable;

class Geometry implements JsonSerializable
{
    public string $type;

    /** @var array<array{number,number}>|array<array<array{number,number}>> */
    public array $coordinates;

    /**
     * @param array<array{number,number}>|array<array<array{number,number}>> $coordinates
     */
    public function __construct(array $coordinates)
    {
        $this->coordinates = $coordinates;
    }

    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'coordinates' => $this->coordinates,
        ];
    }
}
