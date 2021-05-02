# EqualStreetNames - Data process

1. Download data from _OpenStreetMap_ using Overpass

    ```cmd
    php process.php overpass
    ```

    Check the commented source code ([`OverpassCommand.php`](./Command/OverpassCommand.php)) and [`overpass.md`](./docs/overpass.md) for documentation.

1. Download data from _Wikidata_

    ```cmd
    php process.php wikidata
    ```

    Check the commented source code ([`WikidataCommand.php`](./Command/WikidataCommand.php)) and [`wikidata.md`](./docs/wikidata.md) for documentation.

1. Download city boundary geometry

    ```cmd
    php process.php boundary
    ```

    Check the commented source code ([`BoundaryCommand.php`](./Command/BoundaryCommand.php)) for documentation.

1. Generate final GeoJSON files

    ```cmd
    php process.php geojson
    ```

    Check the commented source code ([`GeoJSONCommand.php`](./Command/GeoJSONCommand.php)) and [`geojson.md`](./docs/geojson.md) for documentation.

1. Generate final statistics JSON file and CSV files ([documentation](./docs/statistics.md))

    ```cmd
    php process.php statistics
    ```

    Check the commented source code ([`WikidataCommand.php`](./Command/WikidataCommand.php)) for documentation.

---

All those steps are run sequentially when you run the following command (after replacing `my-country` and `my-city` by the name of your country and city):

```cmd
composer run update-data -- --city=my-country/my-city
```
