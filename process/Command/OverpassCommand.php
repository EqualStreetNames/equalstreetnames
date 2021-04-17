<?php

namespace App\Command;

use ErrorException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OverpassCommand extends Command
{
  protected static $defaultName = 'process:overpass';

  protected const URL = 'https://overpass-api.de/api/interpreter';

  protected string $city;
  protected string $directory;
  protected string $outputDir = 'data/overpass';

  protected function configure()
  {
    $this->setDescription('Download data from OpenStreetMap with Overpass API.');

    $this->addOption('city', 'c', InputOption::VALUE_REQUIRED, 'City directory: <my-country>/<my-city>');
  }

  protected function initialize(InputInterface $input, OutputInterface $output)
  {
    $this->city = $input->getOption('city');

    $this->directory = sprintf('../cities/%s/overpass', $this->city);

    mkdir($this->outputDir, 0777, true);
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    try {
      if (!file_exists($this->directory) || !is_dir($this->directory)) {
        throw new Exception(sprintf('Directory "%s" doesn\'t exist.', $this->directory));
      }

      $output->writeln([
        sprintf('<info>%s</info>', $this->getDescription()),
        sprintf('<comment>City: %s</info>', $this->city),
      ]);

      $relations = self::query(sprintf('%s/relation-full-json', $this->directory));
      $ways = self::query(sprintf('%s/way-full-json', $this->directory));

      file_put_contents(sprintf('%s/relation.json', $this->outputDir), $relations);
      file_put_contents(sprintf('%s/way.json', $this->outputDir), $ways);

      return Command::SUCCESS;
    } catch (Exception $exception) {
      $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

      return Command::FAILURE;
    }
  }

  protected static function query(string $path): string
  {
    if (!file_exists($path) || !is_readable($path)) {
      throw new Exception(sprintf('File "%s" doesn\'t exist or is not readable.', $path));
    }

    $query = file_get_contents($path);

    $url = sprintf('%s?data=%s', self::URL, urlencode($query));

    $client = new \GuzzleHttp\Client();
    $response = $client->request('GET', $url);

    $status = $response->getStatusCode();

    if ($status !== 200) {
      throw new ErrorException($response->getReasonPhrase());
    }

    return (string) $response->getBody();
  }
}
