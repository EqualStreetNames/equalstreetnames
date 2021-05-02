<?php

namespace App\Command;

use ErrorException;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Execute Overpass query for relations and ways using Overpass API and store result.
 *
 * @package App\Command
 */
class OverpassCommand extends AbstractCommand
{
    /** {@inheritdoc} */
    protected static $defaultName = 'overpass';

    /** @var string Filename for the result of Overpass query for relations. */
    public const FILENAME_RELATION = 'relation.json';
    /** @var string Filename for the result of Overpass query for ways. */
    public const FILENAME_WAY = 'way.json';

    /** @var string Filename of Overpass query for relations. */
    protected const OVERPASS_RELATION = 'relation-full-json';
    /** @var string Filename of Overpass query for ways. */
    protected const OVERPASS_WAY = 'way-full-json';

    /** @var string Overpass API URL. */
    protected const URL = 'https://overpass-api.de/api/interpreter';

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Download data from OpenStreetMap with Overpass API.');
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     *
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            parent::execute($input, $output);

            // Check path of Overpass query for relations.
            $relationPath = sprintf('%s/overpass/%s', $this->cityDir, self::OVERPASS_RELATION);
            if (!file_exists($relationPath) || !is_readable($relationPath)) {
                throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable.', $relationPath));
            }
            // Check path of Overpass query for ways.
            $wayPath = sprintf('%s/overpass/%s', $this->cityDir, self::OVERPASS_WAY);
            if (!file_exists($wayPath) || !is_readable($wayPath)) {
                throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable.', $wayPath));
            }

            // Create `overpass` directory to store results.
            $outputDir = sprintf('%s/overpass', self::OUTPUTDIR);
            if (!file_exists($outputDir) || !is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            // Execute Overpass query for relations and store result.
            $overpassR = file_get_contents($relationPath);
            if ($overpassR !== false) {
                self::save($overpassR, sprintf('%s/%s', $outputDir, self::FILENAME_RELATION));
            }
            // Execute Overpass query for ways and store result.
            $overpassW = file_get_contents($wayPath);
            if ($overpassW !== false) {
                self::save($overpassW, sprintf('%s/%s', $outputDir, self::FILENAME_WAY));
            }

            return Command::SUCCESS;
        } catch (Exception $error) {
            $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));

            return Command::FAILURE;
        }
    }

    /**
     * Send request and store result.
     *
     * @param string $query Overpass query.
     * @param string $path Path where to store the result.
     * @return void
     *
     * @throws GuzzleException
     */
    private static function save(string $query, string $path): void
    {
        $url = sprintf('%s?data=%s', self::URL, urlencode($query));

        $client = new \GuzzleHttp\Client();
        $client->request('GET', $url, ['sink' => $path]);
    }
}
