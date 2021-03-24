# EqualStreetNames

This project is coordinated by [Open Knowledge Belgium](https://openknowledge.be/) and [Noms Peut-Être](https://nomspeutetre.wordpress.com/)
with the support of [OpenStreetMap Belgium](https://openstreetmap.be/) and [Wikimedia Belgium](https://wikimedia.be/).

EqualStreetNames is made possible thanks to [equal.brussels](http://equal.brussels/).

## Why EqualStreetNames.Brussels

The names of public spaces (streets, avenues, squares and others) define the identity of a city and how citizens interact with it. The region of Brussels suffers from a major inequality between male and female street names and we want to help fix this.

There are several ways to approach the inequality of street names and leverage a positive change in our society. Ours is with the use of Open Data to create a **map visualizing the streetnames of Brussels by gender**.

Since then, this project has been replicated in several cities across multiple countries.
For a complete list take a look at the table located under **Replicate the EqualStreetNames project for your city**

## How did we make this map

To make this happen, we used [open data](http://opendefinition.org/) - data which can be freely used, modified, and shared by anyone for any purpose - from [OpenStreetMap](https://openstreetmap.org/) and [Wikipedia](https://www.wikipedia.org/).

On 17 February 2020, 60 volunteers gathered to add the Wikidata tags (a tag containing all the information from a Wikipedia page) to the streets on OpenStreetMap. Using Open Data has unlocked new opportunities, the project now being replicable for other cities and the analysis being fully transparent.

The Equal Street Names project is divided into two phases:

- Phase 1:  
  Mapping the inequality of name attributions.
- Phase 2:  
  Organizing one workshop per month in 2020 to provide a list of names that will be published on the platform and act as references for city councils. The names should represent the diversity of Brussels Region, which means with profiles of women; women from the immigration, women of color, transgender and LGBTQIA+

## Data

Data is available in the `data/` directory of each city.
For Burssels there is documentation about the data is in the same folder (see [`README.md`](https://github.com/EqualStreetNames/equalstreetnames-brussels/blob/master/data/README.md)).

## Documentation

See [`docs/README.md`](./docs/README.md)

## Replicate the EqualStreetNames project for your city

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

See [`docs/replicate.md`](./docs/replicate.md)

## Run locally

For more detailed instructions look in [`dev-linux.md`](./dev-linux.md) or [`dev-windows.md`](./dev-windows.md)

1. Clone the repository

   ```cmd
   git clone https://github.com/EqualStreetNames/equalstreetnames.git
   cd equalstreetnames
   ```

1. Clone submodules

   ```cmd
   git submodule update --init --recursive
   ```

1. Install dependencies

   ```cmd
   npm install
   ```

1. Create a [Mapbox token](https://docs.mapbox.com/help/how-mapbox-works/access-tokens/)

1. Create a file named `.env` in the root directory of the project

1. Add the following line to the `.env` file: `MAPBOX_TOKEN=[your Mapbox token]` replacing `[your Mapbox token]` with the token you created

1. Choose the city you want to run locally `npm run serve:country:city` (for example `npm run serve:belgium:brussels`)

1. Once installed and running, go to <http://localhost:1234/index.html>
