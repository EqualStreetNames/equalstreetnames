# Replicate the EqualStreetNames project for your city

The EqualStreetNames project is built so it's (relatively) easy to replicate in any city in the World !  
The data and file specific to a city are hosted in a separated repository.

## Process

The easiest way is to fork/duplicate and existing repository, choose one of the data repository listed above.

Each city repository is a [sub-module](https://git-scm.com/book/en/v2/Git-Tools-Submodules) of the [main repository](https://github.com/openknowledgebe/equalstreetnames). A sub-module is working as a sub-folder in the `cities` folder of the main repository.

### HTML files

The HTML files should be in the `html/` folder.  
You have the `html/index.html` that will show the list of available languages and you have an `index.html` file per language (for Brussels, [`html/en/index.html`](https://github.com/openknowledgebe/equalstreetnames-brussels/blob/master/html/en/index.html), [`html/fr/index.html`](https://github.com/openknowledgebe/equalstreetnames-brussels/blob/master/html/fr/index.html), and [`html/nl/index.html`](https://github.com/openknowledgebe/equalstreetnames-brussels/blob/master/html/nl/index.html)).

You'll have to adapt the content but the most important part is the map settings:

```html
<script>
  app.center = [4.3651, 50.8355];
  app.zoom = 11;
  app.bbox = [4.243544, 50.763726, 4.482277, 50.913384];
  app.countries = "be";
  app.style = "mapbox://styles/mapbox/dark-v10";
  app.init();
</script>
```

- `app.center` will define the default center of the map (longitude and latitude) ; it should be the _center_ of your city ;
- `app.zoom` will define the default zoom level of the map ;
- `app.style` will define the map style ; it can be a Mapbox pre-defined style (see [API Reference](https://docs.mapbox.com/mapbox-gl-js/api/#map)) or your custom style (see [Style Specification](https://docs.mapbox.com/mapbox-gl-js/style-spec/)) ;
- `app.bbox` will filter the geocoder (search for an address) results to a define region ; the format is : min. longitude, min. latitude, max. longitude, max. latitude ;
- `app.countries` will filter the geocoder results to a specific country (or list of countries, separated by a comma) ;

To calculate the bounding box around your city, you can use <https://boundingbox.klokantech.com/>.

### Data (GeoJSON files)

Once you have adapt the user interface, you'll have to generate the data for your city.

#### Overpass query

You'll have to adapt the [Overpass API](https://wiki.openstreetmap.org/wiki/Overpass_API) queries to query the relations and ways from [OpenStreetMap](https://openstreetmap.org).

The Overpass queries are located in the `overpass/` folder.

You can use [Overpass Turbo](https://overpass-turbo.eu/) to help you build the correct queries.

#### Run the download/generation scripts

Once the Overpass queries are adapted to your city, you can run the scripts to download the data from [OpenStreetMap](https://openstreetmap.org) and [Wikidata](https://www.wikidata.org/).

Read the [scripts documentation](./scripts/README.md) and simply run the following command by replacing `mycity` by the name of your city:

```
composer run update-data -- --city=mycity
```

#### Run Locally

You now have everything you need to run the project on your city, you can now follow the "[Run Locally](../README.md#run-locally)" steps from the main README file.

## Let us know

We would be more than happy to include your city in your project !

Create a new [issue](https://github.com/openknowledgebe/equalstreetnames/issues) to let us know about your city or send us a [pull request](https://github.com/openknowledgebe/equalstreetnames/pulls) with your sub-module !
