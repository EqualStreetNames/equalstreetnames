<?php

namespace App\Model\GeoJSON;

use App\Model\Details\Details;
use JsonSerializable;

class Properties implements JsonSerializable
{
    public string $name;
    public ?string $wikidata;
    public ?string $gender;
    public ?string $source;
    /** @var null|Details|Details[] */
    public $details;

    public function jsonSerialize()
    {
        return [
        'name' => $this->name,
        'wikidata' => $this->wikidata,
        'gender' => $this->gender,
        'source' => $this->source,
        'details' => $this->details,
        ];
    }
}
