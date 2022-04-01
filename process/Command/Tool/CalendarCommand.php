<?php

namespace App\Command\Tool;

use App\Exception\FileException;
use Cron\CronExpression;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Check update process calendar.
 *
 * @package App\Command\Tool
 */
class CalendarCommand extends Command
{
    /** {@inheritdoc} */
    protected static $defaultName = 'tool:calendar';

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setDescription('Check update process calendar.');
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $data = [];

            $glob = glob('../cities/*/*');
            if ($glob !== false) {
                foreach ($glob as $directory) {
                    $path = sprintf('%s/.github/workflows/update-data.yml', $directory);
                    if (!file_exists($path) || !is_readable($path)) {
                        throw new FileException(sprintf('File "%s" doesn\'t exist or is not readable.', $path));
                    }

                    $yaml = Yaml::parseFile($path);

                    /** @var CronExpression[] */
                    $calendar = [];
                    foreach ($yaml['on']['schedule'] as $schedule) {
                        $crons = array_values(array_filter($schedule, function ($key): bool {
                            return $key === 'cron';
                        }, ARRAY_FILTER_USE_KEY));
                        $cronExpressions = array_map(function ($c): CronExpression {
                            return new CronExpression($c);
                        }, $crons);
                        $calendar = array_merge($calendar, $cronExpressions);
                    }

                    foreach ($calendar as $cron) {
                        $parts = $cron->getParts();
                        $day = $parts[4];
                        if ($day === '0') {
                            $day = '7';
                        }
                        $time = sprintf('%02s:%02s', $parts[1], $parts[0]);

                        $duplicates = array_filter($data, function ($row) use ($day, $time, $cron): bool {
                            return $row[4]->getNextRunDate()->format('c') === $cron->getNextRunDate()->format('c');
                            // return ($row[1] === $day && $row[3] === $time) || ($row[1] === '*' && $row[3] === $time) || ($day === '*' && $row[3] === $time);
                        });
                        $warning = count($duplicates) > 0 ? '⚠ Duplicate' : null;

                        $data[] = [
                            substr($directory, 10),
                            $day,
                            $parts[2],
                            $time,
                            $cron,
                            // $cron->getNextRunDate()->format('d M Y H:i'),
                            $warning
                        ];
                    }
                }
            }

            $nextRun = array_map(function ($row) {
                return $row[4]->getNextRunDate()->format('c');
            }, $data);
            array_multisort(
                $nextRun,
                SORT_ASC,
                $data
            );

            $display = array_map(function ($row) {
                return [
                    $row[0],
                    $row[1],
                    $row[2],
                    $row[3],
                    $row[4]->getExpression(),
                    $row[4]->getNextRunDate()->format('d M Y H:i'),
                    $row[5],
                ];
            }, $data);

            $table = new Table($output);
            $table->setHeaders(['City', 'Day of week', 'Day of month', 'Time', 'Cron', 'Next run', '']);
            $table->setRows($display);
            $table->render();

            return Command::SUCCESS;
        } catch (Exception $error) {
            $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));

            return Command::FAILURE;
        }
    }
}
