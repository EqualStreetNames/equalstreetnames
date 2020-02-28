# EqualStreetNames.Brussels - Scripts

Scripts: [`wikidata.php`](../../scripts/wikidata.php)

## Generate final GeoJSON files

### Extract data from _OpenStreetMap_ objects

From the files downloaded from _OpenStreetMap_, we extract the following informations:

- Relation (or way)
- Geometry of the street ;
- Streetname
  - `name` tag (bilingual) ;
  - `name:fr` tag (french) ;
  - `name:nl` tag (dutch) ;
- `wikidata` tag ()
- `name:etymology:wikidata` tag

### Extract data from _Wikidata_ objects

### Apply gender information

### Generate files

## Run the script locally

```
composer install

php scripts/geojson.php
```

The `relations.geojson` and `ways.geojson` files will be stored in `static/` directory.
