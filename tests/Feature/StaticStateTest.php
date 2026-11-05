<?php

namespace OZiTAG\Tager\Backend\Crud\Tests\Feature;

use OZiTAG\Tager\Backend\Crud\Jobs\StoreJob;
use OZiTAG\Tager\Backend\Crud\Resources\ModelResource;
use OZiTAG\Tager\Backend\Crud\Tests\Fixtures\Article;
use OZiTAG\Tager\Backend\Crud\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * ModelResource field maps and StoreJob/UpdateJob configuration used to live
 * in static properties that CrudController wrote on every request. Under
 * PHP-FPM one request serves one controller so the sharing was invisible;
 * under a long-lived worker (Octane, queue workers), or in any single request
 * touching two CRUD controllers, the last writer won.
 *
 * Both are now passed per instance. The statics remain only as a fallback for
 * modules that still call setFields()/setConfig() themselves.
 */
class StaticStateTest extends TestCase
{
    #[Test]
    public function two_controllers_each_return_their_own_field_set_in_sequence(): void
    {
        $this->createArticles(1);

        $articles = $this->getJson('/admin/articles')->json('data.0');
        $tags = $this->getJson('/admin/tags')->json('data.0');

        $this->assertSame(['id', 'title', 'isPublished'], array_keys($articles));
        $this->assertSame(['id', 'body'], array_keys($tags));
    }

    #[Test]
    public function serving_a_request_leaves_no_field_map_on_the_shared_resource_class(): void
    {
        $this->createArticles(1);

        $this->getJson('/admin/tags')->assertOk();

        $resource = new ModelResource(Article::query()->first());

        $this->assertSame(
            [],
            $resource->toArray(request()),
            'the request field map must not outlive the request on the shared class'
        );
    }

    #[Test]
    public function a_field_map_passed_per_instance_does_not_affect_the_next_instance(): void
    {
        $this->createArticles(1);
        $article = Article::query()->first();

        $scoped = new ModelResource($article, ['id', 'title']);
        $this->assertSame(['id', 'title'], array_keys($scoped->toArray(request())));

        $plain = new ModelResource($article);
        $this->assertSame([], $plain->toArray(request()));
    }

    #[Test]
    public function the_static_setter_still_works_for_backwards_compatibility(): void
    {
        $this->createArticles(1);
        $article = Article::query()->first();

        ModelResource::setFields(['id']);

        try {
            $this->assertSame(['id'], array_keys((new ModelResource($article))->toArray(request())));

            // an instance field map still wins over the static fallback
            $scoped = new ModelResource($article, ['id', 'title']);
            $this->assertSame(['id', 'title'], array_keys($scoped->toArray(request())));
        } finally {
            ModelResource::setFields([]);
        }
    }

    #[Test]
    public function serving_a_request_leaves_no_config_on_the_shared_store_job_class(): void
    {
        // /admin/tags maps the request's `title` onto the `body` column, which
        // proves the per-instance config reached the job.
        $this->postJson('/admin/tags', ['title' => 'from tags'])->assertStatus(201);

        $this->assertDatabaseHas('articles', ['body' => 'from tags']);

        $property = (new \ReflectionClass(StoreJob::class))->getProperty('config');
        $property->setAccessible(true);

        $this->assertSame(
            [],
            $property->getValue(),
            'StoreJob must not retain controller configuration as static state'
        );
    }

    #[Test]
    public function two_controllers_writing_in_sequence_each_use_their_own_field_map(): void
    {
        // /admin/tags maps title -> body; /admin/articles maps title -> title.
        $this->postJson('/admin/tags', ['title' => 'into body'])->assertStatus(201);
        $this->postJson('/admin/articles', ['title' => 'into title'])->assertStatus(201);

        $this->assertDatabaseHas('articles', ['body' => 'into body']);
        $this->assertDatabaseHas('articles', ['title' => 'into title']);
    }
}
