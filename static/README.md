# EqualStreetNames.Brussels

## Data

### Statistics

Gender is defined based on _Wikidata_ and, if not available in or not linked to wikidata, based on a manual work during the event of the 17th February 2020 (see [documentation](../docs/README.md#get-the-gender-and-data-about-the-person)).

- `gender.csv`: street listing with gender (CSV format)
- `gender.json`: street listing with gender (JSON format)

### Map

- `relations.geojson`: relations from _OpenStreetMap_ consolidated with data from _Wikidata_
- `ways.geojson`: ways from _OpenStreetMap_ consolidated with data from _Wikidata_

#### Data structure

- `id`: relation (or way) identifier in _OpenStreetMap_
- `name`: streetname (bilingual)
- `name:fr`: streetname (french)
- `name:nl`: streetname (dutch)
- `wikidata`: street wikidata identifier
- `etymology`: person wikidata identifier
- `person`
  - `labels`: person name/title
    - `language` (`de|en|fr|nl`)
    - `value`
  - `descriptions`: small description about the person
    - `language` (`de|en|fr|nl`)
    - `value`
  - `gender`: gender (`f|m|x`)
  - `birth`: year of birth
  - `death`: year of death
  - `sitelinks`
    - `site` (`dewiki|enwiki|frwiki|nlwiki`)
    - `title`: Wikipedia page title
    - `badge`
    - `url`: Wikipedia page URL
