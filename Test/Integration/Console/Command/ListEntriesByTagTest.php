<?php

declare(strict_types=1);

namespace Yireo\CacheDebugger\Test\Integration\Console\Command;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Console\CommandListInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Yireo\CacheDebugger\Console\Command\ListEntriesByTag;

class ListEntriesByTagTest extends TestCase
{
    private const TAG = 'YIREO_CACHEDEBUGGER_TEST';

    private CacheInterface $cache;

    protected function setUp(): void
    {
        $this->cache = Bootstrap::getObjectManager()->get(CacheInterface::class);
    }

    protected function tearDown(): void
    {
        $this->cache->clean([self::TAG]);
    }

    public function testCommandIsRegistered(): void
    {
        $commands = Bootstrap::getObjectManager()->get(CommandListInterface::class)->getCommands();
        $command = $commands['yireo_cachedebugger_list_entries_by_tag'] ?? null;

        $this->assertInstanceOf(ListEntriesByTag::class, $command);
        $this->assertSame('cache:debug:list-by-tag', $command->getName());
    }

    public function testListsEntriesMatchingTagCaseInsensitive(): void
    {
        $this->cache->save('foo', 'yireo_cachedebugger_test_1', [self::TAG]);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['tag' => strtolower(self::TAG)]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('YIREO_CACHEDEBUGGER_TEST_1', $commandTester->getDisplay());
    }

    public function testLimitRestrictsNumberOfEntries(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->cache->save('foo', 'yireo_cachedebugger_test_' . $i, [self::TAG]);
        }

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['tag' => self::TAG, '--limit' => '1']);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $lines = array_filter(explode(PHP_EOL, trim($commandTester->getDisplay(), " \f\n\r\t\v\x00")));
        $this->assertCount(1, $lines);
    }

    public function testInvalidTagIsRejected(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['tag' => '../../etc/passwd']);

        $this->assertSame(Command::INVALID, $commandTester->getStatusCode());
        $this->assertStringContainsString('Invalid cache tag', $commandTester->getDisplay());
    }

    public function testInvalidLimitIsRejected(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['tag' => self::TAG, '--limit' => '-1']);

        $this->assertSame(Command::INVALID, $commandTester->getStatusCode());
        $this->assertStringContainsString('Invalid limit', $commandTester->getDisplay());
    }

    private function getCommandTester(): CommandTester
    {
        $command = Bootstrap::getObjectManager()->create(ListEntriesByTag::class);
        return new CommandTester($command);
    }
}
