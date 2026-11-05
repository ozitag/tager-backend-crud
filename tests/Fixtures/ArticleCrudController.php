<?php

namespace OZiTAG\Tager\Backend\Crud\Tests\Fixtures;

use OZiTAG\Tager\Backend\Crud\Actions\IndexAction;
use OZiTAG\Tager\Backend\Crud\Actions\StoreOrUpdateAction;
use OZiTAG\Tager\Backend\Crud\Actions\UpdateAction;
use OZiTAG\Tager\Backend\Crud\Controllers\AdminCrudController;

/**
 * A faithful miniature of a real module controller (cf. BlogAdminCategoriesController):
 * declarative resource fields, a store/update action backed by the generic
 * StoreJob/UpdateJob, move and count enabled.
 */
class ArticleCrudController extends AdminCrudController
{
    protected bool $hasMoveAction = true;

    protected bool $hasCountAction = true;

    public function __construct(ArticleRepository $repository)
    {
        parent::__construct($repository);

        $fields = [
            'id',
            'title',
            'isPublished' => 'is_published:boolean',
        ];

        $this->setResourceFields($fields);

        $this->setFullResourceFields(array_merge($fields, [
            'body',
            'priority',
        ]));

        $this->setResourceFieldsForView('short', ['id', 'title']);

        $jobParams = [
            'repository' => $repository,
            'fields' => [
                'title' => 'title',
                'body' => 'body',
                'is_published' => 'isPublished',
            ],
        ];

        $this->setStoreAction(new StoreOrUpdateAction(ArticleRequest::class, null, $jobParams));
        $this->setUpdateAction(new StoreOrUpdateAction(ArticleRequest::class, null, $jobParams));

        // A custom named update action with no explicit job class: it must
        // fall back to UpdateJob, not StoreJob.
        $this->addAction('republish', new UpdateAction(ArticleRequest::class, null, $jobParams));

        $this->setIndexAction(new IndexAction());
    }
}
