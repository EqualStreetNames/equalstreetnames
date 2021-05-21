const fs = require('fs');
const path = require('path');
const shell = require('shelljs');
const program = require('commander');
const turf = require('@turf/turf');

const version = require('./package.json').version;

program.version(version);

program.option('-s, --serve').action(bundle);

program.parseAsync(process.argv).catch((error) => {
  shell.echo(`Error: ${error}`);
  shell.exit(1);
});

async function bundle (options) {
  const serve = options.serve || false;

  try {
    const polygons = { type: 'FeatureCollection', features: [] };
    const points = { type: 'FeatureCollection', features: [] };

    const citiesDetails = JSON.parse(
      fs.readFileSync(path.resolve('./cities.json'))
    );

    const directory = path.resolve('../cities/');

    const countries = fs
      .readdirSync(directory, { withFileTypes: true })
      .filter((directory) => directory.isDirectory());

    countries.forEach((country) => {
      const cities = fs
        .readdirSync(path.join(directory, country.name), {
          withFileTypes: true
        })
        .filter((directory) => directory.isDirectory());

      cities.forEach((city) => {
        if (
          typeof citiesDetails[country.name] === 'undefined' ||
          typeof citiesDetails[country.name][city.name] === 'undefined'
        ) {
          throw new Error(
            `Details are missing for "${country.name}/${city.name}".`
          );
        }

        const feature = {
          type: 'Feature',
          id: `${country.name}:${city.name}`,
          properties: citiesDetails[country.name][city.name],
          geometry: null
        };

        const metadata = JSON.parse(
          fs.readFileSync(
            path.join(
              directory,
              country.name,
              city.name,
              'data',
              'metadata.json'
            ),
            'utf8'
          )
        );
        feature.properties.statistics = metadata.genders;
        feature.properties.lastUpdate = metadata.update;

        const boundary = fs.readFileSync(
          path.join(
            directory,
            country.name,
            city.name,
            'data',
            'boundary.geojson'
          ),
          'utf8'
        );
        feature.geometry = JSON.parse(boundary);

        polygons.features.push(feature);

        const centroid = turf.centerOfMass(feature.geometry);

        const featurePoint = { ...feature };
        featurePoint.geometry = centroid.geometry;

        points.features.push(featurePoint);

        shell.echo('✔', feature.properties.name);
      });
    });

    shell.rm('-rf', 'dist/');
    shell.mkdir('dist/');

    fs.writeFileSync(
      path.resolve('./dist/cities.json'),
      JSON.stringify(polygons),
      'utf8'
    );
    fs.writeFileSync(
      path.resolve('./dist/cities-point.json'),
      JSON.stringify(points),
      'utf8'
    );

    shell.echo(
      'Total:',
      polygons.features.length,
      'polygons',
      '&',
      points.features.length,
      'points'
    );

    if (serve === true) {
      shell.exec('npm run parcel:serve', { async: true });
    } else {
      shell.exec('npm run parcel:build');
    }
  } catch (error) {
    shell.echo(error.message);
    shell.exit(1);
  }
}
