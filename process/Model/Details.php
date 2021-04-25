<?php

namespace App\Model;

use ArrayObject;
use stdClass;

class LanguageValue {
  public string $language;
  public string $value;
}

class Details {
  public ?string $wikidata;
  public ?bool $person;
  public ?string $gender;
  /** @var null|ArrayObject<string,LanguageValue> */
  public ?ArrayObject $labels;
  /** @var null|ArrayObject<string,LanguageValue> */
  public ?ArrayObject $descriptions;
  /** @var array<string,string> */
  public ?array $nicknames;
  public ?int $birth;
  public ?int $death;
  /** @var null|ArrayObject<string,string> */
  public ?ArrayObject $sitelinks;
  public ?string $image;

  public function __construct(array $details)
  {
    $this->wikidata = $details['wikidata'] ?? null;
    $this->person = $details['person'] ?? null;
    $this->gender = $details['gender'] ?? null;
    $this->labels = $details['labels'] ?? null;
    $this->descriptions = $details['descriptions'] ?? null;
    $this->nicknames = $details['nicknames'] ?? null;
    $this->birth = $details['birth'] ?? null;
    $this->death = $details['death'] ?? null;
    $this->sitelinks = $details['sitelinks'] ?? null;
    $this->image = $details['image'] ?? null;
  }
}
