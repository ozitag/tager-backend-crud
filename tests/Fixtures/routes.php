<?php

use Illuminate\Support\Facades\Route;
use OZiTAG\Tager\Backend\Crud\Tests\Fixtures\ArticleCrudController;
use OZiTAG\Tager\Backend\Crud\Tests\Fixtures\TagCrudController;

Route::group(['prefix' => 'admin/articles'], function () {
    Route::get('/count', [ArticleCrudController::class, 'count']);
    Route::get('/', [ArticleCrudController::class, 'index']);
    Route::get('/{id}', [ArticleCrudController::class, 'view']);
    Route::post('/', [ArticleCrudController::class, 'store']);
    Route::put('/{id}', [ArticleCrudController::class, 'update']);
    Route::delete('/{id}', [ArticleCrudController::class, 'delete']);
    Route::post('/move/{id}/{direction}', [ArticleCrudController::class, 'move'])
        ->where('direction', 'up|down');
    Route::put('/{id}/republish', [ArticleCrudController::class, 'republish']);
});

Route::group(['prefix' => 'admin/tags'], function () {
    Route::get('/', [TagCrudController::class, 'index']);
    Route::post('/', [TagCrudController::class, 'store']);
});
