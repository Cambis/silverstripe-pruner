<?php

namespace Cambis\SilverstripePruner\Tests\Task;

use Cambis\SilverstripePruner\Task\PruneSelectedORMTablesTask;
use Cambis\SilverstripePruner\Task\Source\Extension\TruncatedClassesExtension;
use Cambis\SilverstripePruner\Task\Source\Extension\TruncatedTablesExtension;
use Cambis\SilverstripePruner\Tests\Task\Source\Model\TestRecord;
use Override;
use SilverStripe\Config\Collections\MutableConfigCollectionInterface;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use const PHP_EOL;

final class PruneSelectedORMTablesTaskTest extends SapphireTest
{
    /**
     * @var array<class-string<DataObject>>
     */
    public static $extra_dataobjects = [
        TestRecord::class,
    ];

    protected $usesDatabase = true;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        DB::query('DROP TABLE IF EXISTS TestRecord_Foo;');
        DB::query('CREATE TABLE TestRecord_Foo (Bar varchar(255) not null);');
    }

    public function testTruncateClass(): void
    {
        $record = TestRecord::create();
        $record->write();

        $this->assertCount(1, TestRecord::get());

        Config::withConfig(static function (MutableConfigCollectionInterface $config): void {
            $config->set(
                PruneSelectedORMTablesTask::class,
                'truncated_classes',
                [TestRecord::class]
            );

            $task = PruneSelectedORMTablesTask::create();
            $task->run(new HTTPRequest('GET', '/', [
                'confirm' => 1,
            ]));
        });

        $this->assertCount(0, TestRecord::get());
    }

    public function testTruncateClassExtension(): void
    {
        $record = TestRecord::create();
        $record->write();

        $this->assertCount(1, TestRecord::get());

        Config::withConfig(static function (MutableConfigCollectionInterface $config): void {
            $config->set(
                PruneSelectedORMTablesTask::class,
                'extensions',
                [TruncatedClassesExtension::class]
            );

            $task = PruneSelectedORMTablesTask::create();
            $task->run(new HTTPRequest('GET', '/', [
                'confirm' => 1,
            ]));
        });

        $this->assertCount(0, TestRecord::get());
    }

    public function testTruncateTable(): void
    {
        DB::query('INSERT INTO "TestRecord_Foo" ("Bar") VALUES (\'Baz\');');

        $this->assertSame(1, (int) DB::query('SELECT COUNT(*) FROM TestRecord_Foo;')->value());

        Config::withConfig(static function (MutableConfigCollectionInterface $config): void {
            $config->set(
                PruneSelectedORMTablesTask::class,
                'truncated_tables',
                ['TestRecord_Foo']
            );

            $task = PruneSelectedORMTablesTask::create();
            $task->run(new HTTPRequest('GET', '/', [
                'confirm' => 1,
            ]));
        });

        $this->assertSame(0, (int) DB::query('SELECT COUNT(*) FROM TestRecord_Foo;')->value());
    }

    public function testTruncateTableExtension(): void
    {
        DB::query('INSERT INTO "TestRecord_Foo" ("Bar") VALUES (\'Baz\');');

        $this->assertSame(1, (int) DB::query('SELECT COUNT(*) FROM TestRecord_Foo;')->value());

        Config::withConfig(static function (MutableConfigCollectionInterface $config): void {
            $config->set(
                PruneSelectedORMTablesTask::class,
                'extensions',
                [TruncatedTablesExtension::class]
            );

            $task = PruneSelectedORMTablesTask::create();
            $task->run(new HTTPRequest('GET', '/', [
                'confirm' => 1,
            ]));
        });

        $this->assertSame(0, (int) DB::query('SELECT COUNT(*) FROM TestRecord_Foo;')->value());
    }

    public function testRunInProduction(): void
    {
        $director = new class extends Director implements TestOnly {
            public static function isLive(): bool
            {
                return true;
            }
        };

        Injector::inst()->registerService($director, Director::class);

        $this->expectOutputString('🚨 This task cannot be run in a production environment! 🚨' . PHP_EOL);
        $task = PruneSelectedORMTablesTask::create();
        $task->run(new HTTPRequest('GET', '/'));
    }

    public function testRunWithoutConfirmation(): void
    {
        $this->expectOutputString('⚠️ Are you sure? Please add ?confirm=1 to the URL to confirm. ⚠️' . PHP_EOL);
        $task = PruneSelectedORMTablesTask::create();
        $task->run(new HTTPRequest('GET', '/'));
    }
}
