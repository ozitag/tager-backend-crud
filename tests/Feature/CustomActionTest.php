<?php

namespace OZiTAG\Tager\Backend\Crud\Tests\Feature;

use OZiTAG\Tager\Backend\Crud\Tests\Fixtures\Article;
use OZiTAG\Tager\Backend\Crud\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Regression: a custom UpdateAction registered through addAction() with no
 * explicit job class used to fall back to StoreJob, which inserts a new row
 * instead of updating the one the route addresses.
 */
class CustomActionTest extends TestCase
{
    #[Test]
    public function a_custom_update_action_updates_the_addressed_row(): void
    {
        $this->createArticles(1);
        $article = Article::query()->first();

        $this->putJson('/admin/articles/' . $article->id . '/republish', [
            'title' => 'Republished',
        ])->assertSuccessful();

        $this->assertSame(1, Article::query()->count(), 'a new row must not be inserted');
        $this->assertSame('Republished', $article->refresh()->title);
    }

    #[Test]
    public function a_custom_update_action_leaves_unmentioned_fields_alone(): void
    {
        $this->createArticles(1);
        $article = Article::query()->first();

        $this->putJson('/admin/articles/' . $article->id . '/republish', [
            'title' => 'Renamed only',
        ])->assertSuccessful();

        $this->assertSame('Body 1', $article->refresh()->body);
    }
}
