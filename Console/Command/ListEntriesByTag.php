<?php

namespace Yireo\CacheDebugger\Console\Command;

use Composer\Console\Input\InputOption;
use Magento\Framework\App\Cache;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\Cache\ConfigInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListEntriesByTag extends Command
{
    private Cache $cache;

    public function __construct(
        Cache $cache,
        string $name = null)
    {
        parent::__construct($name);
        $this->cache = $cache;
    }

    /**
     * Initialization of the command.
     */
    protected function configure()
    {
        $this->setName('cache:debug:list-by-tag');
        $this->setDescription('List caching entries by tag');
        $this->addArgument('tag', InputOption::VALUE_REQUIRED, 'Cache tag');
        parent::configure();
    }

    /**
     * CLI command description.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheTag = (string)$input->getArgument('tag');
        $cacheBackend = $this->cache->getFrontend()->getBackend();

        foreach($cacheBackend->getIdsMatchingTags([$cacheTag]) as $cacheId) {
            $output->writeln($cacheId);
        }

        return Command::SUCCESS;
    }
}
