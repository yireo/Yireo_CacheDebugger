<?php

declare(strict_types=1);

namespace Yireo\CacheDebugger\Console\Command;

use Magento\Framework\App\CacheInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class ListEntriesByTag extends Command
{
    public function __construct(
        private readonly CacheInterface $cache,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * Initialization of the command.
     */
    protected function configure()
    {
        $this->setName('cache:debug:list-by-tag');
        $this->setDescription('List caching entries by tag');
        $this->addArgument('tag', InputArgument::REQUIRED, 'Cache tag');
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Maximum number of entries (0 for no limit)', '0');
        parent::configure();
    }

    /**
     * CLI command description.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheTag = trim((string)$input->getArgument('tag'), " \f\n\r\t\v\x00");
        if (!preg_match('/^[A-Za-z0-9_]+$/D', $cacheTag)) {
            $output->writeln('<error>Invalid cache tag: only use characters A-Z, a-z, 0-9 and _</error>');
            return Command::INVALID;
        }

        $limit = (string)$input->getOption('limit');
        if (!ctype_digit($limit)) {
            $output->writeln('<error>Invalid limit: use a positive number or 0</error>');
            return Command::INVALID;
        }

        try {
            $cacheIds = $this->cache->getFrontend()->getLowLevelFrontend()
                ->getIdsMatchingTags([strtoupper($cacheTag)]);
        } catch (Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            return Command::FAILURE;
        }

        if ((int)$limit > 0) {
            $cacheIds = array_slice($cacheIds, 0, (int)$limit);
        }

        foreach ($cacheIds as $cacheId) {
            $output->writeln((string)$cacheId, OutputInterface::OUTPUT_RAW);
        }

        return Command::SUCCESS;
    }
}
