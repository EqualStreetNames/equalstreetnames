<?php

namespace App\Command;

use ErrorException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BoundaryCommand extends Command
{
  protected static $defaultName = 'boundary';

  protected const URL = 'http://polygons.openstreetmap.fr/get_geojson.py';

  protected string $city;
  protected string $configPath;
  protected string $outputDir;
  protected int $relationId;

  protected function configure()
  {
    $this->setDescription('Download city boundary from OpenStreetMap.');

    $this->addOption('city', 'c', InputOption::VALUE_REQUIRED, 'City directory: <my-country>/<my-city>', 'undefined/undefined');
  }

  protected function initialize(InputInterface $input, OutputInterface $output)
  {
    $this->city = $input->getOption('city');

    $this->configPath = sprintf('../cities/%s/config.php', $this->city);
    $this->outputDir = sprintf('../cities/%s/data', $this->city);

    if (!file_exists($this->outputDir) || !is_dir($this->outputDir)) {
      mkdir($this->outputDir, 0777, true);
    }
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $output->writeln([
      sprintf('<info>%s</info>', $this->getDescription()),
      sprintf('<comment>City: %s</comment>', $this->city),
    ]);

    try {
      if (!file_exists($this->configPath) || !is_readable($this->configPath)) {
        throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable.', $this->configPath));
      }

      $config = require $this->configPath;

      if (!isset($config['relationId']) || !is_int($config['relationId'])) {
        throw new ErrorException(sprintf('"relationId" parameter is missing in "%s".', $this->configPath));
      }

      $boundary = self::query($config['relationId']);

      file_put_contents(sprintf('%s/boundary.geojson', $this->outputDir), $boundary);

      return Command::SUCCESS;
    } catch (Exception $error) {
      $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));

      return Command::FAILURE;
    }
  }

  protected static function query(int $id): string
  {
    $url = sprintf('%s?id=%d', self::URL, $id);

    $client = new \GuzzleHttp\Client();
    $response = $client->request('GET', $url);

    $status = $response->getStatusCode();

    if ($status !== 200) {
      throw new ErrorException($response->getReasonPhrase());
    }

    return (string) $response->getBody();
  }
}
