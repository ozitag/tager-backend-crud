<?php

namespace OZiTAG\Tager\Backend\Crud\Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use OZiTAG\Tager\Backend\Core\CoreServiceProvider;
use OZiTAG\Tager\Backend\HttpCache\HttpCacheServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CoreServiceProvider::class,
            HttpCacheServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        tap($app->make(Repository::class), function (Repository $config) {
            $config->set('database.default', 'testing');
            $config->set('database.connections.testing', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);
        });
    }

    protected function defineRoutes($router): void
    {
        require __DIR__ . '/Fixtures/routes.php';
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    protected function createArticles(int $count = 3): void
    {
        foreach (range(1, $count) as $i) {
            Fixtures\Article::create([
                'title' => 'Article ' . $i,
                'body' => 'Body ' . $i,
                'is_published' => $i % 2 === 0,
                'priority' => $i,
            ]);
        }
    }
}
