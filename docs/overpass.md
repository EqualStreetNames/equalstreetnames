# Equal Street Names (Brussels)

## How to get the streets of Brussels from _OpenStreetMap_ ?

We used the [Overpass API](https://overpass-api.de/) to query the _OpenStretMap_ database.

### Relations `associatedStreet`

This is not the case everywhere in the world but in Brussels most of the streets have their [`associatedStreet` relation](https://wiki.openstreetmap.org/wiki/Relation:associatedStreet).

We requested from _OpenStreetMap_ all the objects of [type `relation`](https://wiki.openstreetmap.org/wiki/Relation) tagged with [`type=associatedStreet`](https://wiki.openstreetmap.org/wiki/Relation:associatedStreet) that are in Brussels Region and in the municipality 21001 (Anderlecht - see [list of municipalities](./municipalities.md)).

```
[out:json][timeout:120];
( area["name"="Région de Bruxelles-Capitale - Brussels Hoofdstedelijk Gewest"]["admin_level"="4"]; )->.b;
( area["ref:INS"="21001"]["admin_level"="8"](area.b); )->.a;
( relation["type"="associatedStreet"](area.a)(area.b); );
out body;
>;
out skel qt;
```

[Open this query in Overpass-Turbo](http://overpass-turbo.eu/s/QES)

### Ways `highway`

Just to be sure we have all the streets (including the streets that do not have [`associatedStreet` relation](https://wiki.openstreetmap.org/wiki/Relation:associatedStreet)), we also requested from _OpenStreetMap_ all the objects of [type `way`](https://wiki.openstreetmap.org/wiki/Way) tagged with [`highway`](https://wiki.openstreetmap.org/wiki/Key:highway) and [`name`](https://wiki.openstreetmap.org/wiki/Key:name) (except [`highway=bus_stop`](https://wiki.openstreetmap.org/wiki/Tag:highway=bus_stop) and [`highway=service`](https://wiki.openstreetmap.org/wiki/Tag:highway=service)) that are in Brussels Region and in the municipality 21001 (Anderlecht - see [list of municipalities](./municipalities.md)).

```
[out:json][timeout:120];
( area["name"="Région de Bruxelles-Capitale - Brussels Hoofdstedelijk Gewest"]["admin_level"="4"]; )->.b;
( area["ref:INS"="21001"]["admin_level"="8"](area.b); )->.a;
( way["highway"]["name"]["highway"!="bus_stop"]["highway"!="service"](area.a)(area.b); );
out body;
>;
out skel qt;
```

[Open this query in Overpass-Turbo](http://overpass-turbo.eu/s/QEN)

### Cleaning/Consolidating

[...to be documented...]
