<?php

declare(strict_types=1);

namespace Yireo\CacheDebugger\Test\Unit\Console\Command;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Yireo\CacheDebugger\Console\Command\ListEntriesByTag;
use Magento\Framework\Cache\Core;

class ListEntriesByTagTest extends TestCase
{
    /**
     * @dataProvider getInvalidTags
     */
    #[DataProvider('getInvalidTags')]
    public function testInvalidTagIsRejected(string $tag): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->never())->method('getFrontend');

        $commandTester = new CommandTester(new ListEntriesByTag($cache));
        $commandTester->execute(['tag' => $tag]);

        $this->assertSame(Command::INVALID, $commandTester->getStatusCode());
    }

    public static function getInvalidTags(): array
    {
        return [
            ['../../etc/passwd'],
            ['internal-foo'],
            ['foo bar'],
            ["foo\nbar"],
            ['<info>foo</info>'],
            [''],
        ];
    }

    public function testValidTagIsUppercasedAndPassedToLowLevelFrontend(): void
    {
        $lowLevelFrontend = $this->createMock(Core::class);
        $lowLevelFrontend->expects($this->once())
            ->method('getIdsMatchingTags')
            ->with(['CONFIG_SCOPES'])
            ->willReturn(['A', 'B', '<info>C</info>']);

        $frontend = $this->createMock(FrontendInterface::class);
        $frontend->method('getLowLevelFrontend')->willReturn($lowLevelFrontend);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('getFrontend')->willReturn($frontend);

        $commandTester = new CommandTester(new ListEntriesByTag($cache));
        $commandTester->execute(['tag' => ' config_Scopes ', '--limit' => '3']);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertSame('A' . PHP_EOL . 'B' . PHP_EOL . '<info>C</info>' . PHP_EOL, $commandTester->getDisplay());
    }
}
