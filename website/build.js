const shell = require('shelljs');

let city = '';
const cityIndex = process.argv.indexOf('-c');
if (cityIndex > -1) {
  city = process.argv[cityIndex + 1];
}

let script = 'build';
const scriptIndex = process.argv.indexOf('-s');
if (scriptIndex > -1) {
  script = process.argv[scriptIndex + 1];
}

if (city.length > 0) {
  const directory = `../cities/${city}`;

  if (shell.test('-e', directory) === true) {
    shell.rm('-rf', ['assets/', 'dist/', 'public/', 'static/']);

    shell.mkdir('assets/', 'public/', 'static/')

    shell.cp(`${directory}/assets/*`, 'assets/');
    shell.cp('-r', `${directory}/html/*`, 'public/');
    shell.cp(`${directory}/data/*`, 'static/');

    shell.rm('-rf', `../dist/${city}`)

    shell.exec(`npm run ${script} -- --out-dir "../dist/${city}"`);
  } else {
    shell.echo(`Error: Path ${directory} does not exist.`);
    shell.exit(1);
  }
}

shell.exit();
