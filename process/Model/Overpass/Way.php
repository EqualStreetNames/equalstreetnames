<?php

namespace App\Model\Overpass;

class Way extends Element
{
    public string $type = 'way';

  /** @var int[] */
    public array $nodes = [];
}
