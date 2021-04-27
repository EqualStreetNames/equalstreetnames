<?php

namespace App\Model\GeoJSON;

use App\Model\Details;

class Properties
{
    public string $name;
    public ?string $wikidata;
    public ?string $gender;
    public ?string $source;
  /** @var null|Details|Details[] */
    public $details;
}
