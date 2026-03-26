<?php

use App\Http\Controllers\EntryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
| All routes here are automatically prefixed with /api
|--------------------------------------------------------------------------
*/

// apiResource generates 5 RESTful routes automatically:
// GET    /api/entries          → EntryController@index   (list)
// POST   /api/entries          → EntryController@store   (create)
// GET    /api/entries/{entry}  → EntryController@show    (single)
// PUT    /api/entries/{entry}  → EntryController@update  (update)
// DELETE /api/entries/{entry}  → EntryController@destroy (delete)
Route::apiResource('entries', EntryController::class);
