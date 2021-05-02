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
 * Based on `relationId` from `config.php` file, download city boundary geometry as GeoJSON file.
 *
 * @package App\Command
 */
class BoundaryCommand extends AbstractCommand
{
    /** {@inheritdoc} */
    protected static $defaultName = 'boundary';

    /** @var string Script URL (by OpenStreetMap France). */
    protected const URL = 'http://polygons.openstreetmap.fr/get_geojson.py';

    /** @var string Filename for the result. */
    protected const FILENAME = 'boundary.geojson';

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

        $this->setDescription('Download city boundary from OpenStreetMap.');
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

            if (!isset($this->config->relationId)) {
                throw new ErrorException('"relationId" parameter is missing or is invalid in "config.php".');
            }

            self::save($this->config->relationId, sprintf('%s/%s', $this->cityOutputDir, self::FILENAME));

            return Command::SUCCESS;
        } catch (Exception $error) {
            $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));

            return Command::FAILURE;
        }
    }

    /**
     * Send request and store result.
     *
     * @param int $id OpenStreetMap relation identifier.
     * @param string $path Path where to store the result.
     * @return void
     *
     * @throws GuzzleException
     */
    private static function save(int $id, string $path): void
    {
        $url = sprintf('%s?id=%d', self::URL, $id);

        $client = new \GuzzleHttp\Client();
        $client->request('GET', $url, ['sink' => $path]);
    }
}
