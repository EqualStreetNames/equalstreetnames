# Setting up a development environment on Linux

This guide tries to go through some of the requirements for developing on this project,
but feel free to adopt it to your own needs.

## Requirements

* [Git](https://git-scm.com/download/linux)
* [Node.js](https://nodejs.org/en/download/)
* [PHP](https://www.php.net/manual/en/install.unix.php)
* [Composer](https://getcomposer.org/download/)
* An editor of your choice

Most requirements can simply be installed by running `sudo apt-get install git wget nodejs npm php-cli php-zip unzip curl`
Composer can then be installed using `curl -sS https://getcomposer.org/installer |php` and `sudo mv composer.phar /usr/local/bin/composer`

## Setting up

Getting a working version of EqualStreetNames consists of a couple of steps:

1. Clone the repository
`git clone https://github.com/EqualStreetNames/equalstreetnames.git`

2. Go to the folder
`cd equalstreetnames`

3. Initialize submodules
`git submodule update --init --recursive`

4. Install with npm
`npm install`

5. Install with composer
`composer install`

You should now have a completely working version of EqualStreetNames to work on.

## Starting up a server

You can start up a server using `npm run server:myCountry:myCity`,
where you replace myCountry by an available country (for example 'belgium'),
and myCity by an available city (for example 'brussels').
