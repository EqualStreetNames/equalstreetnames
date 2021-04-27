<?php

namespace App\Model\Overpass\Relation;

class Member
{
  /** @var 'node'|'way'|'relation' */
    public string $type;

    public int $ref;

    public string $role;
}
