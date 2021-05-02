<?php

namespace App\Model\Details;

class Details
{
    public ?string $wikidata;
    public ?bool $person;
    public ?string $gender;
    /** @var null|array<string,LanguageValue> */
    public ?array $labels;
    /** @var null|array<string,LanguageValue> */
    public ?array $descriptions;
    /** @var array<string,string> */
    public ?array $nicknames;
    public ?int $birth;
    public ?int $death;
    /** @var null|array<string,string> */
    public ?array $sitelinks;
    public ?string $image;

    public function __construct(array $details)
    {
        $this->wikidata = $details['wikidata'] ?? null;
        $this->person = $details['person'] ?? null;
        $this->gender = $details['gender'] ?? null;
        $this->labels = $details['labels'] ?? [];
        $this->descriptions = $details['descriptions'] ?? [];
        $this->nicknames = $details['nicknames'] ?? [];
        $this->birth = $details['birth'] ?? null;
        $this->death = $details['death'] ?? null;
        $this->sitelinks = $details['sitelinks'] ?? [];
        $this->image = $details['image'] ?? null;
    }
}
