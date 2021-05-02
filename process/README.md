# EqualStreetNames

## Scripts used by the data update process

1. Download data from _OpenStreetMap_ using Overpass ([documentation](./docs/overpass.md))

    ```cmd
    php scripts/overpass/relation.php
    php scripts/overpass/way.php
    ```

1. Download data from _Wikidata_ ([documentation](./docs/wikidata.md))

    ```cmd
    php scripts/wikidata.php
    ```

1. Generate final GeoJSON files ([documentation](./docs/geojson.md))

    ```cmd
    php scripts/geojson.php
    ```

1. Generate final statistics JSON file and CSV files ([documentation](./docs/statistics.md))

    ```cmd
    php scripts/statistics.php
    ```

All those steps are runned sequentially when you run the following command (after replacing `my-country` and `my-city` by the name of your country and city):

```cmd
composer run update-data -- --city=my-country/my-city
```
