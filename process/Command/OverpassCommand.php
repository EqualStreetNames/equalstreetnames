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

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Download data from OpenStreetMap with Overpass API.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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

            $outputDir = sprintf('%s/overpass', $this->processOutputDir);
            if (!file_exists($outputDir) || !is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $overpassR = file_get_contents($relationPath);
            if ($overpassR !== false) {
                self::save($overpassR, sprintf('%s/relation.json', $outputDir));
            }
            $overpassW = file_get_contents($wayPath);
            if ($overpassW !== false) {
                self::save($overpassW, sprintf('%s/way.json', $outputDir));
            }

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
