<?php

namespace OZiTAG\Tager\Backend\Crud\Tests\Feature;

use OZiTAG\Tager\Backend\Crud\Tests\Fixtures\Article;
use OZiTAG\Tager\Backend\Crud\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CrudWriteTest extends TestCase
{
    // ----- view -------------------------------------------------------

    #[Test]
    public function view_returns_the_full_resource_field_set(): void
    {
        $this->createArticles(1);
        $id = Article::query()->value('id');

        $response = $this->getJson('/admin/articles/' . $id);

        $response->assertOk();
        $this->assertSame(
            ['id', 'title', 'isPublished', 'body', 'priority'],
            array_keys($response->json('data'))
        );
        $this->assertSame('Body 1', $response->json('data.body'));
    }

    #[Test]
    public function view_returns_404_for_an_unknown_id(): void
    {
        $this->getJson('/admin/articles/9999')->assertStatus(404);
    }

    // ----- store ------------------------------------------------------

    #[Test]
    public function store_persists_the_mapped_fields_and_returns_the_full_resource(): void
    {
        $response = $this->postJson('/admin/articles', [
            'title' => 'Created',
            'body' => 'Created body',
            'isPublished' => true,
        ]);

        $response->assertStatus(201);
        $this->assertSame('Created', $response->json('data.title'));
        $this->assertTrue($response->json('data.isPublished'));

        $this->assertDatabaseHas('articles', [
            'title' => 'Created',
            'body' => 'Created body',
            'is_published' => true,
        ]);
    }

    #[Test]
    public function store_assigns_an_incrementing_priority_when_move_is_enabled(): void
    {
        $this->postJson('/admin/articles', ['title' => 'First', 'isPublished' => false])->assertStatus(201);
        $this->postJson('/admin/articles', ['title' => 'Second', 'isPublished' => false])->assertStatus(201);

        $this->assertSame(1, Article::query()->where('title', 'First')->value('priority'));
        $this->assertSame(2, Article::query()->where('title', 'Second')->value('priority'));
    }

    #[Test]
    public function store_rejects_a_payload_that_fails_validation(): void
    {
        $response = $this->postJson('/admin/articles', ['body' => 'no title']);

        $response->assertStatus(400);
        $response->assertJsonPath('errors.title.code', 'VALIDATION_FIELD_REQUIRED');
        $this->assertSame(0, Article::query()->count());
    }

    /**
     * Regression: StoreJob used to map every configured field unconditionally
     * ($data[$field] = $this->request[$requestField]), so an optional field
     * absent from the payload landed in the INSERT as an explicit NULL. That
     * overrode the column's database default and made NOT NULL columns fail
     * with an integrity violation. Absent fields are now left out entirely.
     */
    #[Test]
    public function store_omits_a_mapped_field_absent_from_the_payload_so_the_column_default_applies(): void
    {
        $this->postJson('/admin/articles', ['title' => 'Partial'])->assertStatus(201);

        $this->assertFalse(
            Article::query()->where('title', 'Partial')->value('is_published'),
            'the NOT NULL column should have taken its database default'
        );
    }

    #[Test]
    public function store_still_writes_a_field_sent_explicitly_as_null(): void
    {
        $this->postJson('/admin/articles', ['title' => 'Explicit', 'body' => null])
            ->assertStatus(201);

        $this->assertNull(Article::query()->where('title', 'Explicit')->value('body'));
    }

    /**
     * The same guard protects updates: a partial payload no longer wipes the
     * columns it does not mention.
     */
    #[Test]
    public function update_leaves_untouched_a_mapped_field_absent_from_the_payload(): void
    {
        $this->createArticles(1);
        $article = Article::query()->first();

        $this->putJson('/admin/articles/' . $article->id, ['title' => 'Only title'])
            ->assertSuccessful();

        $article->refresh();
        $this->assertSame('Only title', $article->title);
        $this->assertSame('Body 1', $article->body, 'body must survive a partial update');
    }

    #[Test]
    public function store_rejects_a_value_over_the_declared_maximum(): void
    {
        $response = $this->postJson('/admin/articles', ['title' => str_repeat('x', 101)]);

        $response->assertStatus(400);
        $this->assertSame(0, Article::query()->count());
    }

    // ----- update -----------------------------------------------------

    #[Test]
    public function update_changes_only_the_mapped_fields(): void
    {
        $this->createArticles(1);
        $article = Article::query()->first();

        $response = $this->putJson('/admin/articles/' . $article->id, [
            'title' => 'Renamed',
            'body' => 'Rewritten',
            'isPublished' => true,
        ]);

        $response->assertSuccessful();

        $article->refresh();
        $this->assertSame('Renamed', $article->title);
        $this->assertSame('Rewritten', $article->body);
        $this->assertTrue($article->is_published);
        $this->assertSame(1, $article->priority, 'update must not touch priority');
    }

    #[Test]
    public function update_returns_404_for_an_unknown_id(): void
    {
        $this->putJson('/admin/articles/9999', ['title' => 'x'])->assertStatus(404);
    }

    #[Test]
    public function update_validates_the_payload(): void
    {
        $this->createArticles(1);
        $article = Article::query()->first();

        $this->putJson('/admin/articles/' . $article->id, ['title' => ''])
            ->assertStatus(400);

        $this->assertSame('Article 1', $article->refresh()->title);
    }

    // ----- delete -----------------------------------------------------

    #[Test]
    public function delete_removes_the_row(): void
    {
        $this->createArticles(2);
        $article = Article::query()->first();

        $this->deleteJson('/admin/articles/' . $article->id)->assertOk();

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        $this->assertSame(1, Article::query()->count());
    }

    #[Test]
    public function delete_returns_404_for_an_unknown_id(): void
    {
        $this->deleteJson('/admin/articles/9999')->assertStatus(404);
    }

    // ----- move -------------------------------------------------------

    #[Test]
    public function move_up_swaps_priority_with_the_previous_row(): void
    {
        $this->createArticles(3);
        $second = Article::query()->where('priority', 2)->first();

        $this->postJson('/admin/articles/move/' . $second->id . '/up')->assertOk();

        $this->assertSame(1, $second->refresh()->priority);
        $this->assertSame(2, Article::query()->where('title', 'Article 1')->value('priority'));
    }

    #[Test]
    public function move_down_swaps_priority_with_the_next_row(): void
    {
        $this->createArticles(3);
        $second = Article::query()->where('priority', 2)->first();

        $this->postJson('/admin/articles/move/' . $second->id . '/down')->assertOk();

        $this->assertSame(3, $second->refresh()->priority);
        $this->assertSame(2, Article::query()->where('title', 'Article 3')->value('priority'));
    }

    #[Test]
    public function moving_the_first_row_up_is_a_no_op(): void
    {
        $this->createArticles(3);
        $first = Article::query()->where('priority', 1)->first();

        $this->postJson('/admin/articles/move/' . $first->id . '/up')->assertOk();

        $this->assertSame(1, $first->refresh()->priority);
        $this->assertSame([1, 2, 3], Article::query()->orderBy('id')->pluck('priority')->all());
    }
}
