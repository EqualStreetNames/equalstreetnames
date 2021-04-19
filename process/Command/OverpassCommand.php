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

      $relationPath = sprintf('%s/overpass/relation-full-json', $this->cityDir);
      if (!file_exists($relationPath) || !is_readable($relationPath)) {
        throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable.', $relationPath));
      }
      $wayPath = sprintf('%s/overpass/way-full-json', $this->cityDir);
      if (!file_exists($wayPath) || !is_readable($wayPath)) {
        throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable.', $wayPath));
      }

      self::save(
        file_get_contents($relationPath),
        sprintf('%s/overpass/relation.json', $this->processOutputDir)
      );
      self::save(
        file_get_contents($wayPath),
        sprintf('%s/overpass/way.json', $this->processOutputDir)
      );

      return Command::SUCCESS;
    } catch (Exception $error) {
      $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));

      return Command::FAILURE;
    }
  }

  private static function save(string $query, string $path): void
  {
    $url = sprintf('%s?data=%s', self::URL, urlencode($query));

    $client = new \GuzzleHttp\Client();
    $client->request('GET', $url, ['sink' => $path]);
  }
}
