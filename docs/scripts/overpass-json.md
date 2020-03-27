# EqualStreetNames.Brussels - Scripts

Scripts: [`relation.php`](../../scripts/overpass/relation.php) + [`way.php`](../../scripts/overpass/way.php)

## Download data from [_OpenStreetMap_](https://openstreetmap.org/) (overpass)

We used the [Overpass API](https://overpass-api.de/) to query the _OpenStreetMap_ database.

### Relations `associatedStreet`

This is not the case everywhere in the world, but in Brussels most of the streets have an [`associatedStreet` relation](https://wiki.openstreetmap.org/wiki/Relation:associatedStreet).

We queried from _OpenStreetMap_ all the objects of [type `relation`](https://wiki.openstreetmap.org/wiki/Relation) tagged with [`type=associatedStreet`](https://wiki.openstreetmap.org/wiki/Relation:associatedStreet) or [`type=street`](https://wiki.openstreetmap.org/wiki/Relation:street) that are in Brussels Region ([INS-NIS](https://statbel.fgov.be/) code `04000` & _Wikidata_ identifier [`Q240`](https://www.wikidata.org/wiki/Q240)).

We also queried from _OpenStreetMap_ all the objects of [type `multipolygon`](https://wiki.openstreetmap.org/wiki/Relation) tagged with [`place`](https://wiki.openstreetmap.org/wiki/Key:place) or [`highway`](https://wiki.openstreetmap.org/wiki/Key:highway) that have a [name](https://wiki.openstreetmap.org/wiki/Key:highway) and are in Brussels Region ([INS-NIS](https://statbel.fgov.be/) code `04000` & _Wikidata_ identifier [`Q240`](https://www.wikidata.org/wiki/Q240)).

```
[out:json][timeout:300];
( area["admin_level"="4"]["ref:INS"="04000"]["wikidata"="Q240"]; )->.a;
(
    relation["type"="associatedStreet"](area.a);
    relation["type"="street"](area.a);
    relation["type"="multipolygon"]["place"]["name"](area.a);
    relation["type"="multipolygon"]["highway"]["name"](area.a);
);
out body;
>;
out skel qt;
```

[Open this query in Overpass-Turbo](http://overpass-turbo.eu/s/RO6)

### Ways `highway` + `place=square`

Just to be sure we have all the streets (including the streets that do not have [`associatedStreet` relation](https://wiki.openstreetmap.org/wiki/Relation:associatedStreet)), we also requested from _OpenStreetMap_ all the objects of [type `way`](https://wiki.openstreetmap.org/wiki/Way) tagged with [`highway`](https://wiki.openstreetmap.org/wiki/Key:highway) and [`name`](https://wiki.openstreetmap.org/wiki/Key:name) (except [`highway=bus_stop`](https://wiki.openstreetmap.org/wiki/Tag:highway=bus_stop) and [`highway=service`](https://wiki.openstreetmap.org/wiki/Tag:highway=service)) and [`place=square`](https://wiki.openstreetmap.org/wiki/Tag:place=square) that are in Brussels Region ([INS-NIS](https://statbel.fgov.be/) code `04000` & _Wikidata_ identifier [`Q240`](https://www.wikidata.org/wiki/Q240)).

```
[out:json][timeout:300];
( area["admin_level"="4"]["ref:INS"="04000"]["wikidata"="Q240"]; )->.a;
( way["highway"]["name"]["highway"!="bus_stop"]["highway"!="service"](area.a); );
out body;
>;
out skel qt;
```

[Open this query in Overpass-Turbo](http://overpass-turbo.eu/s/R96)

## Run the script locally

```
composer install

php scripts/overpass/relation.php
php scripts/overpass/way.php
```

The `full.json` file containing all the `associatedStreet` relations will be stored in `data/overpass/relation/` directory.  
The `full.json` file containing all the `highway` ways will be stored in `data/overpass/way/` directory.
