<?php

namespace App\Model\Wikidata;

class Claims
{
    /** @var null|array<object> image */
    public ?array $P18;
    /** @var null|array<object> sex or gender */
    public ?array $P21;
    /** @var null|array<object> instance of */
    public ?array $P31;
    /** @var null|array<object> named after */
    public ?array $P138;
    /** @var null|array<object> subclass of */
    public ?array $P279;
    /** @var null|array<object> date of birth */
    public ?array $P569;
    /** @var null|array<object> date of death */
    public ?array $P570;
    /** @var null|array<object> nickname */
    public ?array $P1449;
}
