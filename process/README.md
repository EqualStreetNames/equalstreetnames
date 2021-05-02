# EqualStreetNames

## Scripts used by the data update process

1. Download data from _OpenStreetMap_ using Overpass ([documentation](./docs/overpass.md))

    ```cmd
    php process.php overpass
    ```

1. Download data from _Wikidata_ ([documentation](./docs/wikidata.md))

    ```cmd
    php process.php wikidata
    ```

1. Download city boundary geometry

    ```cmd
    php process.php boundary
    ```

1. Generate final GeoJSON files ([documentation](./docs/geojson.md))

    ```cmd
    php process.php geojson
    ```

1. Generate final statistics JSON file and CSV files ([documentation](./docs/statistics.md))

    ```cmd
    php process.php statistics
    ```

All those steps are runned sequentially when you run the following command (after replacing `my-country` and `my-city` by the name of your country and city):

```cmd
composer run update-data -- --city=my-country/my-city
```
