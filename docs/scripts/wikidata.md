# EqualStreetNames.Brussels - Scripts

Scripts: [`wikidata.php`](../../scripts/wikidata.php)

## Download data from [_Wikidata_](https://www.wikidata.org/)

For every `associatedStreet` relations and `highway` ways that have a [`wikidata` tag](https://wiki.openstreetmap.org/wiki/Key:wikidata) or a [`name:etymology:wikidata` tag](https://wiki.openstreetmap.org/wiki/Key:name:etymology:wikidata), we download the data from _Wikidata_ (JSON format).

## Run the script locally

```shell
composer install

php scripts/wikidata.php
```

The JSON files will be stored in `data/wikidata/` directory.
