# EqualStreetNames

This document aims to describe the process of EqualStreetNames.
Many of the examples describe here are based on Brussels, but can be applied to any other city.

## Process

1. Get all the streets in a city from _OpenStreetMap_
2. Use the [`name:etymology:wikidata` tag](https://wiki.openstreetmap.org/wiki/Key:name:etymology:wikidata) to query the information about the person mentionned in the streetname
3. Use the data from _Wikidata_ to determine the gender (and more) of the person

## Data quality

The process defined here above assumes that we have all the streets of our city in _OpenStreetMap_ and that all the streets of our city in _OpenStreetMap_ have a [`name:etymology:wikidata` tag](https://wiki.openstreetmap.org/wiki/Key:name:etymology:wikidata) if the streetname mentions a person.

### All the streets

First it's important to make sure we have all the streets of our city in _OpenStreetMap_. In the case of Brussels the streets listing from _OpenStreetMap_ was compared to the streets listing from [_UrbIS_](https://bric.brussels/en/our-solutions/urbis-solutions/urbis-data) (official data from the Brussels Region).

Alternatives for other regions can be the Flemish [Wegenregister](https://overheid.vlaanderen.be/informatie-vlaanderen/producten-diensten/wegenregister), the Wallonian [PICC](https://geoportail.wallonie.be/catalogue/b795de68-726c-4bdf-a62a-a42686aa5b6f.html), the Dutch [NWB](https://nationaalwegenbestand.nl/), or any official government source.

A few streets were missing and manually added to _OpenStreetMap_.

### All the streets tagged

At the beginning of the project (February 2019), around 5% of the streets of Brussels in _OpenStreetMap_ had a [`name:etymology:wikidata` tag](https://wiki.openstreetmap.org/wiki/Key:name:etymology:wikidata).

Of course, not all the streets refer to a person (or an entity) and thus need a [`name:etymology:wikidata` tag](https://wiki.openstreetmap.org/wiki/Key:name:etymology:wikidata).

To link all the streets that refer to a person to the equivalent _Wikidata_ item, we organized an event were we asked 100 people to manually find the _Wikidata_ item (or the _Wikipedia_ page) about the person from the streetname.

To avoid any issue and to simplify the workflow, the 100 people didn't edit _Wikipedia_, _Wikidata_, or _OpenStreetMap_.  
The result of their work has been tagged manually in _OpenStreetMap_ by [OpenStreetMap Belgium](https://openstreetmap.be/) volunteers.

## Get the gender (and data about the person)

### `name:etymology:wikidata` tag

If there is a [`name:etymology:wikidata` tag](https://wiki.openstreetmap.org/wiki/Key:name:etymology:wikidata) in _OpenStreetMap_, the process follow that identifier to query that item from _Wikidata_.

We consider to be a person, the _Wikidata_ items that are "instance of" (property [`P31`](https://www.wikidata.org/wiki/Property:P31)):

- human ([`Q5`](https://www.wikidata.org/wiki/Q5))
- mononymous person ([`Q2985549`](https://www.wikidata.org/wiki/Q2985549))
- human biblical figure ([`Q20643955`](https://www.wikidata.org/wiki/Q20643955))

In the _Wikidata_ object, the sex or gender is defined by the property [`P21`](https://www.wikidata.org/wiki/Property:P21).

## Scripts

See [`process/README.md`](../process/README.md)
