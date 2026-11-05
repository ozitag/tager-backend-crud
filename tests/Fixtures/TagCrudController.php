<?php

namespace OZiTAG\Tager\Backend\Crud\Tests\Fixtures;

use OZiTAG\Tager\Backend\Crud\Actions\IndexAction;
use OZiTAG\Tager\Backend\Crud\Actions\StoreOrUpdateAction;
use OZiTAG\Tager\Backend\Crud\Controllers\AdminCrudController;

/**
 * A second CRUD controller over the same table but with a different field
 * map, used to observe cross-controller static state.
 */
class TagCrudController extends AdminCrudController
{
    public function __construct(ArticleRepository $repository)
    {
        parent::__construct($repository);

        $this->setResourceFields(['id', 'body']);
        $this->setFullResourceFields(['id', 'body']);

        $this->setStoreAction(new StoreOrUpdateAction(ArticleRequest::class, null, [
            'repository' => $repository,
            'fields' => ['body' => 'title'],
        ]));

        $this->setIndexAction(new IndexAction());
    }
}
