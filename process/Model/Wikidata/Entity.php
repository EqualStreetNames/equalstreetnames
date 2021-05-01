<?php

namespace App\Model\Wikidata;

class Entity
{
    public string $id;

    public object $labels;

    public object $descriptions;

    public object $sitelinks;

    /** @var Claims */
    public object $claims;
}
