# EqualStreetNames - Scripts

## Scripts

- [`geojson.php`](../../process/scripts/geojson.php)

## Generate final GeoJSON files

### Extract data from _OpenStreetMap_ objects

From the files downloaded from _OpenStreetMap_, we extract the following informations:

- Relation (or way)
- Geometry of the street
- `name` tag (bilingual)
- `wikidata` tag (wikidata identifier for the street)
- `name:etymology:wikidata` tag (wikidata identifier for the person)

### Extract data from _Wikidata_ objects

From the files downloaded from _OpenStreetMap_, we extract the following informations:

- `instance of` ([`P31`](https://www.wikidata.org/wiki/Property:P31)) or `subclass of` ([`P279`](https://www.wikidata.org/wiki/Property:P279))
- `labels` (in the languages chosen)
- `descriptions` (in the languages chosen)
- `nickname` ([`P1449`](https://www.wikidata.org/wiki/Property:P1449))
- `sitelinks` (in the languages chosen)
- `sex or gender` ([`P21`](https://www.wikidata.org/wiki/Property:P21))
- `date of birth` ([`P569`](https://www.wikidata.org/wiki/Property:P569))
- `date of death` ([`P570`](https://www.wikidata.org/wiki/Property:P570))
- `image` ([`P18`](https://www.wikidata.org/wiki/Property:P18))

## Run the script locally

```cmd
composer install

php scripts/geojson.php
```

The `relations.geojson` and `ways.geojson` files will be stored in the city `data/` directory.
