<?php

namespace App\Model\Overpass;

use App\Model\Overpass\Relation\Member;

class Relation extends Element {
  public string $type = 'relation';

  /** @var Member[] */
  public array $members = [];
}
