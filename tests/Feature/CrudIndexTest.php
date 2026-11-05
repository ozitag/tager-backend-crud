<?php

namespace OZiTAG\Tager\Backend\Crud\Tests\Feature;

use OZiTAG\Tager\Backend\Crud\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * End-to-end cover for the index route: the single most used endpoint in
 * every admin module, and the one that stitches together the repository
 * contracts, the pagination helper and ModelResource field mapping.
 */
class CrudIndexTest extends TestCase
{
    #[Test]
    public function it_returns_the_declared_short_resource_fields(): void
    {
        $this->createArticles(1);

        $response = $this->getJson('/admin/articles');

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['id', 'title', 'isPublished']]]);
        $this->assertSame(
            ['id', 'title', 'isPublished'],
            array_keys($response->json('data.0')),
            'index must not leak fields declared only on the full resource'
        );
    }

    #[Test]
    public function it_maps_a_snake_case_column_onto_its_declared_alias_and_casts_it(): void
    {
        $this->createArticles(2);

        $data = $this->getJson('/admin/articles')->json('data');

        $this->assertIsBool($data[0]['isPublished']);
        $this->assertFalse($data[0]['isPublished']);
        $this->assertTrue($data[1]['isPublished']);
    }

    #[Test]
    public function it_paginates_and_reports_meta(): void
    {
        $this->createArticles(5);

        $response = $this->getJson('/admin/articles?pageNumber=2&pageSize=2');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertSame(5, $response->json('meta.total'));
        $this->assertSame(2, $response->json('meta.page.number'));
        $this->assertSame(2, $response->json('meta.page.size'));
        $this->assertSame(3, (int) $response->json('meta.page.count'));
    }

    #[Test]
    public function it_supports_offset_based_pagination(): void
    {
        $this->createArticles(5);

        $response = $this->getJson('/admin/articles?pageOffset=3&pageLimit=10');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertSame(['offset' => 3, 'limit' => 10], $response->json('meta.page'));
    }

    #[Test]
    public function it_applies_the_search_query(): void
    {
        $this->createArticles(3);

        $response = $this->getJson('/admin/articles?query=Article 2');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Article 2', $response->json('data.0.title'));
    }

    #[Test]
    public function it_applies_a_filter(): void
    {
        $this->createArticles(4);

        $response = $this->getJson('/admin/articles?filter[published]=1');

        $response->assertOk();
        $titles = array_column($response->json('data'), 'title');
        $this->assertSame(['Article 2', 'Article 4'], $titles);
    }

    #[Test]
    public function it_applies_sorting(): void
    {
        $this->createArticles(3);

        $response = $this->getJson('/admin/articles?sort=-title');

        $response->assertOk();
        $this->assertSame(
            ['Article 3', 'Article 2', 'Article 1'],
            array_column($response->json('data'), 'title')
        );
    }

    #[Test]
    public function the_view_parameter_selects_an_alternative_field_set(): void
    {
        $this->createArticles(1);

        $response = $this->getJson('/admin/articles?view=short');

        $response->assertOk();
        $this->assertSame(['id', 'title'], array_keys($response->json('data.0')));
    }

    /**
     * Tager replaces Laravel's validation response: status 400 (not 422) and
     * every field error carries a machine-readable `code`. The admin UI reads
     * those codes, so the envelope is a contract, not an implementation detail.
     */
    #[Test]
    public function an_invalid_pagination_parameter_returns_the_tager_validation_envelope(): void
    {
        $this->createArticles(1);

        $response = $this->getJson('/admin/articles?pageNumber=0&pageSize=10');

        $response->assertStatus(400);
        $response->assertJsonPath('errors.pageNumber.code', 'VALIDATION_MIN_NUMERIC');
        $this->assertArrayHasKey('message', $response->json('errors.pageNumber'));
    }

    #[Test]
    public function the_count_route_reports_the_total(): void
    {
        $this->createArticles(4);

        $response = $this->getJson('/admin/articles/count');

        $response->assertOk();
        $this->assertSame(4, $response->json('data.count'));
    }
}
