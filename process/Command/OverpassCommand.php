<?php

namespace App\Command;

use ErrorException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OverpassCommand extends AbstractCommand
{
  protected static $defaultName = 'overpass';

  protected const URL = 'https://overpass-api.de/api/interpreter';

  protected function configure()
  {
    parent::configure();

    $this->setDescription('Download data from OpenStreetMap with Overpass API.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    try {
      parent::execute($input, $output);

      $relations = self::query(sprintf('%s/overpass/relation-full-json', $this->cityDir));
      $ways = self::query(sprintf('%s/overpass/way-full-json', $this->cityDir));

      file_put_contents(sprintf('%s/overpass/relation.json', $this->processOutputDir), $relations);
      file_put_contents(sprintf('%s/overpass/way.json', $this->processOutputDir), $ways);

      return Command::SUCCESS;
    } catch (Exception $error) {
      $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));

      return Command::FAILURE;
    }
  }

  protected static function query(string $path): string
  {
    if (!file_exists($path) || !is_readable($path)) {
      throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable.', $path));
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
