# EqualStreetNames

This project is coordinated by [Open Knowledge Belgium](https://openknowledge.be/)
with the support of [OpenStreetMap Belgium](https://openstreetmap.be/) and [Wikimedia Belgium](https://wikimedia.be/).

EqualStreetNames is made possible thanks to [equal.brussels](http://equal.brussels/).

## Why

The names of public spaces (streets, avenues, squares and others) define the identity of a city and how citizens interact with it. The region of Brussels suffers from a major inequality between male and female street names and we want to help fix this.

There are several ways to approach the inequality of street names and leverage a positive change in our society. Ours is with the use of Open Data to create a **map visualizing the streetnames of a city by gender**.

The project start with Brussels, Belgium in March 2020 and since then, this project has been replicated in several cities across multiple countries.

| Country | City      | Link                                                | Data Repository                                                                              | Maintainer                                          |
|---------|-----------|-----------------------------------------------------|----------------------------------------------------------------------------------------------|-----------------------------------------------------|
| 🇧🇪    | Brussels  | <https://equalstreetnames.brussels/>                | [equalstreetnames-brussels](https://github.com/EqualStreetNames/equalstreetnames-brussels)   | [@jbelien](https://github.com/jbelien/)             |
| 🇧🇪    | Brugge    | <https://brugge.equalstreetnames.be/>               | [equalstreetnames-brugge](https://github.com/EqualStreetNames/equalstreetnames-brugge)       | [@jbelien](https://github.com/jbelien/)             |
| 🇧🇪    | Gent      | <https://gent.equalstreetnames.be/>                 | [equalstreetnames-gent](https://github.com/EqualStreetNames/equalstreetnames-gent)           | [@jbelien](https://github.com/jbelien/)             |
| 🇧🇪    | Leuven    | <https://leuven.equalstreetnames.be/>               | [equalstreetnames-leuven](https://github.com/EqualStreetNames/equalstreetnames-leuven)       | [@jbelien](https://github.com/jbelien/)             |
| 🇧🇪    | Liège     | <https://liege.equalstreetnames.be/>                | [equalstreetnames-liege](https://github.com/EqualStreetNames/equalstreetnames-liege)         | [@jbelien](https://github.com/jbelien/)             |
| 🇧🇪    | Mons      | <https://mons.equalstreetnames.be/>                 | [equalstreetnames-mons](https://github.com/EqualStreetNames/equalstreetnames-mons)           | [@jbelien](https://github.com/jbelien/)             |
| 🇧🇪    | Namur     | <https://namur.equalstreetnames.be/>                | [equalstreetnames-namur](https://github.com/EqualStreetNames/equalstreetnames-namur)         | [@jbelien](https://github.com/jbelien/)             |
| 🇧🇪    | Nivelles  | <https://nivelles.equalstreetnames.be/>             | [equalstreetnames-nivelles](https://github.com/EqualStreetNames/equalstreetnames-nivelles)   | [@jbelien](https://github.com/jbelien/)             |
| 🇩🇪    | Berlin    | <https://equalstreetnames-berlin.openstreetmap.de/> | [equalstreetnames-berlin](https://github.com/EqualStreetNames/equalstreetnames-berlin)       | [@gislars](https://github.com/gislars/)             |
| 🇩🇪    | Leipzig   | <https://leipzig.equalstreetnames.eu/>              | [equalstreetnames-leipzig](https://github.com/EqualStreetNames/equalstreetnames-leipzig)     |                                                     |
| 🇩🇪    | Munich    |                                                     | [equalstreetnames-munich](https://github.com/EqualStreetNames/equalstreetnames-munich)       |                                                     |
| 🇳🇱    | Assen     | <https://assen.equalstreetnames.eu/>                | [equalstreetnames-assen](https://github.com/EqualStreetNames/equalstreetnames-assen)         | [@robinlinde](https://github.com/robinlinde/)       |
| 🇳🇱    | Groningen | <https://groningen.equalstreetnames.eu/>            | [equalstreetnames-groningen](https://github.com/EqualStreetNames/equalstreetnames-groningen) | [@robinlinde](https://github.com/robinlinde/)       |
| 🇷🇸    | Belgrade  | <https://naziviulica.openstreetmap.rs/>             | [equalstreetnames-belgrade](https://github.com/EqualStreetNames/equalstreetnames-belgrade)   | [@stalker314314](https://github.com/stalker314314/) |

## How

To make this happen, we used [open data](http://opendefinition.org/) - data which can be freely used, modified, and shared by anyone for any purpose - from [OpenStreetMap](https://openstreetmap.org/) and [Wikipedia](https://www.wikipedia.org/).

For more details, see [`docs/README.md`](./docs/README.md)

## Data

Data is available in the `data/` directory of each city (see [`cities` directory](https://github.com/EqualStreetNames/equalstreetnames/tree/master/cities)).

Data is automatically updated once a week.

Following data is available for each city:

- `gender.csv` : List of streetnames in CSV (Comma-separated values) format with streetname, gender, and Wikidata item ;
- `other.csv` : List of streetnames in CSV (Comma-separated values) format that are not related to a person (with Wikidata item if available) ;
- `relations.geojson` + `ways.geojson` : Streets in [GeoJSON format](https://geojson.org/) with streetname, gender, Wikidata item and details (when available) ;
- `statistics.json` : Number of streetnames for each gender:
  - `F` : cisgender female ;
  - `M` : cisgender male ;
  - `FX` : transgender female ;
  - `MX` : transgender male ;
  - `X` : intersex ;
  - `NB` : non-binary ;
  - `+` : multiple ;
  - `?` : unknown ;
  - `-` : not related to a person ;
- `boundary.geojson` : Boundary of the city in [GeoJSON format](https://geojson.org/) (only the streets that are inside this boundary are processed) ;

## Replicate the EqualStreetNames project for your city

See our `equalstreetnames-template` repository: <https://github.com/EqualStreetNames/equalstreetnames-template>

## Install & Run locally

See [`INSTALL.md`](./INSTALL.md)
