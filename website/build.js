const Parcel = require('parcel-bundler');
const path = require('path');
const shell = require('shelljs');
const program = require('commander');
const version = require('./package.json').version;

program.version(version);

program.option('-c, --city <path>').option('-s, --serve').action(bundle);

program.parse(process.argv);

async function bundle (options) {
  const city = options.city;
  const serve = options.serve || false;

  const directory = `../cities/${city}`;

  if (shell.test('-e', directory) === true) {
    shell.rm('-rf', ['assets/', 'dist/', 'public/', 'static/']);

    shell.mkdir('assets/', 'public/', 'static/');

    shell.cp(`${directory}/assets/*`, 'assets/');
    shell.cp('-r', `${directory}/html/*`, 'public/');
    shell.cp(`${directory}/data/*`, 'static/');

    shell.rm('-rf', `../dist/${city}`);

    const options = {
      global: 'app',
      outDir: path.join(__dirname, './dist', city)
    };

    if (serve === true) {
      process.env.NODE_ENV = process.env.NODE_ENV || 'development';

      const bundler = new Parcel(path.join(__dirname, './public/index.html'), {
        ...options
      });
      bundler.on('buildError', () => { shell.exit(1); });

      await bundler.serve();
    } else {
      process.env.NODE_ENV = process.env.NODE_ENV || 'production';

      const bundler = new Parcel(path.join(__dirname, './public/index.html'), {
        ...options,
        production: true
      });
      bundler.on('buildError', () => { shell.exit(1); });

      bundler.bundle();
    }
  } else {
    shell.echo(`Error: Path ${directory} does not exist.`);
    shell.exit(1);
  }
}
