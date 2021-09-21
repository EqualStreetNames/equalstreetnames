const fs = require('fs');
const path = require('path');
const shell = require('shelljs');
const program = require('commander');
const turfBBox = require('@turf/bbox');
const turfHelpers = require('@turf/helpers');

const bbox = turfBBox.default; // @see https://github.com/Turfjs/turf/issues/1932

const version = require('./package.json').version;

program.version(version);

program.option('-c, --city <path>').option('-s, --serve').action(bundle);

program
  .parseAsync(process.argv)
  .catch(error => {
    shell.echo(`Error: ${error}`);
    shell.exit(1);
  });

async function bundle (options) {
  const city = options.city;
  const serve = options.serve || false;

  const directory = `../cities/${city}`;
  const outDir = path.join(__dirname, 'dist', city);

  if (shell.test('-e', directory) === true) {
    shell.rm('-rf', ['assets', 'dist', 'public', 'static', outDir]);

    shell.mkdir('assets', 'public', 'static');
    shell.mkdir('-p', outDir);

    shell.cp(path.join(directory, 'assets', '*'), 'assets');
    shell.cp('-r', path.join(directory, 'html', '*'), 'public');
    shell.cp(path.join(directory, 'data', '*.geojson'), 'static');

    const boundary = JSON.parse(fs.readFileSync(path.resolve(directory, 'data', 'boundary.geojson')));
    const bounds = bbox(turfHelpers.feature(boundary));

    const metadata = JSON.parse(fs.readFileSync(path.resolve(directory, 'data', 'metadata.json')));

    const static = { bounds, statistics: metadata.genders, lastUpdate: metadata.update };

    fs.writeFileSync(
      path.join('static', 'static.json'),
      JSON.stringify(static),
      'utf8'
    );

    const file = path.join(__dirname, 'public', 'index.html');

    if (serve === true) {
      shell.exec(`parcel serve "${file}" --dist-dir "${outDir}"`, { async: true });
    } else {
      if (shell.exec(`parcel build "${file}" --dist-dir "${outDir}"`).code !== 0) {
        shell.echo(`Error: Build failed`);
        shell.exit(1);
      }
    }
  } else {
    shell.echo(`Error: Path ${directory} does not exist.`);
    shell.exit(1);
  }
}
