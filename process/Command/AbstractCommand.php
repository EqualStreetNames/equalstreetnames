<?php

namespace App\Command;

use App\Model\Config\Config;
use ErrorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

abstract class AbstractCommand extends Command
{
    /** @var string|null City name and folder (format: `my-country/my-city`). */
    protected ?string $city;

    /** @var string Path of city directory. */
    protected string $cityDir;

    /** @var string Path of city data directory. */
    protected string $cityOutputDir;

    /** @var Config City configuration (using `config.php` file). */
    protected Config $config;

    /** @var string[][] Data from CSV file. */
    protected array $csv = [];

    /** @var string Process output directory. */
    protected const OUTPUTDIR = 'data';

    /** @var string Configuration filename. */
    protected const CONFIG_FILENAME = 'config.php';

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->addOption('city', 'c', InputOption::VALUE_REQUIRED, 'City directory: <my-country>/<my-city>');
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('red'));
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $city = $input->getOption('city');

        // If `city` option is not defined, list available cities and ask the user to choose
        if (is_null($city) || !is_string($city)) {
            $helper = $this->getHelper('question');

            $glob = glob('../cities/*/*');
            if ($glob !== false) {
                $cities = array_map(function ($path): string {
                    return substr($path, 10);
                }, $glob);

                $question = new ChoiceQuestion('Choose a city: ', $cities);
                $question->setAutocompleterValues($cities);

                $this->city = $helper->ask($input, $output, $question);
            }
        } else {
            $this->city = $city;
        }

        $output->writeln([
            sprintf('<info>%s</info>', $this->getDescription()),
            sprintf('<comment>City: %s</comment>', $this->city),
        ]);

        $this->cityDir = sprintf('../cities/%s', $this->city);
        $this->cityOutputDir = sprintf('%s/%s', $this->cityDir, self::OUTPUTDIR);
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     *
     * @throws ErrorException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check if directory for city exist.
        if (!file_exists($this->cityDir) || !is_dir($this->cityDir)) {
            throw new ErrorException(sprintf('Directory "%s" doesn\'t exist.', $this->cityDir));
        }

        // Create `data` folder in city directory.
        if (!file_exists($this->cityOutputDir) || !is_dir($this->cityOutputDir)) {
            mkdir($this->cityOutputDir, 0777, true);
        }
        // Create `data` folder in process directory.
        if (!file_exists(self::OUTPUTDIR) || !is_dir(self::OUTPUTDIR)) {
            mkdir(self::OUTPUTDIR, 0777, true);
        }

        // Check if `config.php` file exist.
        $configPath = sprintf('%s/%s', $this->cityDir, self::CONFIG_FILENAME);
        if (!file_exists($configPath) || !is_readable($configPath)) {
            throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable.', $configPath));
        }
        // Read configuration.
        $configArray = require $configPath;
        $this->config = new Config($configArray);
    }
}
