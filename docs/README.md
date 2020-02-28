# EqualStreetNames.Brussels

## Process

1. Get all the streets in Brussels from _OpenStreetMap_
2. Use the [`name:etymology:wikidata` tag](https://wiki.openstreetmap.org/wiki/Key:name:etymology:wikidata) to query the information about the person mentionned in the streetname
3. Use the data from _Wikidata_ to determine the gender (and more) of the person

## Data quality

The process defined here above assume that we have all the streets of Brussels in _OpenStreetMap_ and that all the streets of Brussels in _OpenStreetMap_ have a [`name:etymology:wikidata` tag](https://wiki.openstreetmap.org/wiki/Key:name:etymology:wikidata) if the streetname mentions a person.

### All the streets

To make sure we have all the streets of Brussels in _OpenStreetMap_, I compared the streets listing from _OpenStreetMap_ to the streets listing from [_UrbIS_](https://bric.brussels/en/our-solutions/urbis-solutions/urbis-data) (official data from the Brussels Region).

A few streets were missing and manually added to _OpenStreetMap_.

### All the streets tagged

At the beginning of the project (February 2019), around 5% of the streets of Brussels in _OpenStreetMap_ had a [`name:etymology:wikidata` tag](https://wiki.openstreetmap.org/wiki/Key:name:etymology:wikidata).

Of course, not all the streets refer to a person (or an entity) and thus need a [`name:etymology:wikidata` tag](https://wiki.openstreetmap.org/wiki/Key:name:etymology:wikidata).

To link all the streets that refer to a person to the equivalent _Wikidata_ item, we organized an event were we asked 100 people to manually find the _Wikidata_ item (or the _Wikipedia_ page) about the person from the streetname.

To avoid any issue and to simplify the workflow, the 100 people didn't edit _Wikipedia_, _Wikidata_, or _OpenStreetMap_.  
The result of their work has been tagged manually in _OpenStreetMap_ by [OpenStreetMap Belgium](https://openstreetmap.be/) volunteers. (**work in progress**).

## Get the gender (and data about the person)

### `name:etymology:wikidata` tag

If there is a `name:etymology:wikidata` tag in _OpenStreetMap_, the process query that item from _Wikidata_.

...

### No `name:etymology:wikidata` tag

...

## Documentation

- [List of municipalities in Brussels](./municipalities.md)
- [Scripts](./scripts/README.md)
