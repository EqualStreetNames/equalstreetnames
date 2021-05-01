<?php

namespace App\Model\Config;

class GenderRelationWay
{
    /** @var array<string,string> */
    public array $relation;
    /** @var array<string,string> */
    public array $way;

    public function __construct(array $relation, array $way)
    {
        $this->relation = $relation;
        $this->way = $way;
    }
}
