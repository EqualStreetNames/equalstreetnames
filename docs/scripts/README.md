# EqualStreetNames.Brussels

## Scripts

### Map

1. [Download data from _OpenStreetMap_ (overpass)](./overpass-json.md)
1. [Download data from _Wikidata_](./wikidata.md)
1. [Generate final GeoJSON files](./geojson.md)
1. [Generate final statistics JSON file](./statistics.md)

#### Run locally

1. `composer install`
1. `php scripts/overpass/relation.php` ([documentation](./overpass-json.md))
1. `php scripts/overpass/way.php` ([documentation](./overpass-json.md))
1. `php scripts/wikidata.php` ([documentation](./wikidata.md))
1. `php scripts/geojson.php` ([documentation](./geojson.md))
1. `php scripts/statistics.php` ([documentation](./statistics.md))

Or you can just run (after replacing `mycity` by the name of your city):

```
composer run update-data -- --city=mycity
```

### Streets listing

1. [Download data from _OpenStreetMap_ (overpass)](./overpass-csv.md)
1. [Create street streets listing database](./database.md)
1. [Generate street listing (per municipality)](./listing.md)
