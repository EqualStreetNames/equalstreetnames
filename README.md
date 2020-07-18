# EqualStreetNames.Brussels

This project is coordinated by [Open Knowledge Belgium](https://openknowledge.be/) and [Noms Peut-Être](https://nomspeutetre.wordpress.com/)
with the support of [OpenStreetMap Belgium](https://openstreetmap.be/) and [Wikimedia Belgium](https://wikimedia.be/).

EqualStreetNames is made possible thanks to [equal.brussels](http://equal.brussels/).

## Why EqualStreetNames.Brussels?

The names of public spaces (streets, avenues, squares and others) define the identity of a city and how citizens interact with it. The region of Brussels suffers from a major inequality between male and female street names and we want to help fix this.

There are several ways to approach the inequality of street names and leverage a positive change in our society. Ours is with the use of Open Data to create a **map visualizing the streetnames of Brussels by gender**.

## How did we make this map?

To make this happen, we used [open data](http://opendefinition.org/) - data which can be freely used, modified, and shared by anyone for any purpose - from [OpenStreetMap](https://openstreetmap.org/) and [Wikipedia](https://www.wikipedia.org/).

On 17 February 2020, 60 volunteers gathered to add the Wikidata tags (a tag containing all the information from a Wikipedia page) to the streets on OpenStreetMap. Using Open Data has unlocked new opportunities, the project now being replicable for other cities and the analysis being fully transparent.

The Equal Street Names project is divided into two phases:

- Phase 1:  
  Mapping the inequality of name attributions.
- Phase 2:  
  Organizing one workshop per month in 2020 to provide a list of names that will be published on the platform and act as references for city councils. The names should represent the diversity of Brussels Region, which means with profiles of women; women from the immigration, women of color, transgender and LGBTQIA+

## Data

Data are available in the [`static/` directory](./static).  
Documentation about those data is available in the same folder (see [`README.md`](./static/#readme)).

## Documentation

See [`docs/README.md`](./docs/README.md)

## Replicate the EqualStreetNames project for your city

| Country | City     | Link                               | Data Repository                                                                           | Maintainer     |
| ------- | -------- | ---------------------------------- | ----------------------------------------------------------------------------------------- | -------------- |
| 🇧🇪      | Brussels | https://equalstreetnames.brussels/    | [equalstreetnames-brussels](https://github.com/openknowledgebe/equalstreetnames-brussels) | [@jbelien](https://github.com/jbelien/) |
| 🇷🇸      | Belgrade | https://naziviulica.openstreetmap.rs/ | [equalstreetnames-belgrade](https://github.com/stalker314314/equalstreetnames-belgrade)   | [@stalker314314](https://github.com/stalker314314/) |
| 🇳🇱      | Assen | https://esn.rlin.eu/    | [equalstreetnames-assen](https://github.com/robinlinde/equalstreetnames-assen) | [@robinlinde](https://github.com/robinlinde/) |
| 🇩🇪      | Berlin | https://equalstreetnames-berlin.openstreetmap.de/ | [equalstreetnames-berlin](https://github.com/gislars/equalstreetnames-berlin) | [@gislars](https://github.com/gislars/) |

See [`docs/replicate.md`](./docs/replicate.md)

## Run locally

1. Clone the repository

   ```
   git clone https://github.com/openknowledgebe/equalstreetnames.git
   cd equalstreetnames
   ```

1. Install dependencies

   ```
   npm install
   ```

1. Create a [Mapbox token](https://docs.mapbox.com/help/how-mapbox-works/access-tokens/)

1. Create a file named `.env` in the root directory of the project

1. Add the following line to the `.env` file: `MAPBOX_TOKEN=[your Mapbox token]` replacing `[your Mapbox token]` with the token you created

1. Choose the city you want to run locally :

   1. For Brussels, run

      ```
      npm run serve:brussels
      ```

   1. For Belgrade, run

      ```
      npm run serve:belgrade
      ```

   1. For Assen, run

      ```
      npm run serve:assen
      ```

   1. For Berlin, run

      ```
      npm run serve:berlin
      ```

1. Once installed and running, go to <http://localhost:1234/index.html>
