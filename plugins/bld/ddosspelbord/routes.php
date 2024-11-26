<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use bld\ddosspelbord\classes\middleware\ReqAccesToken;
use bld\ddosspelbord\classes\api\v1\TargetsController;
/**
 * Custom Routes
 */
//These routes only work with a bearer token
Route::middleware(ReqAccesToken::class)->group(function () {

    Route::get('/api/v1/ddostests', 'bld\ddosspelbord\classes\api\v1\GameboardController@GetData');
    Route::get('/api/v1/ddostests/{id}', 'bld\ddosspelbord\classes\api\v1\GameboardController@GetDataByID');
    Route::get('/api/v1/measurementtypes', 'bld\ddosspelbord\classes\api\v1\TypesController@GetMeasurementTypes');
    Route::get('/api/v1/measurementtypes/{id}', 'bld\ddosspelbord\classes\api\v1\TypesController@GetMeasurementTypesByID');

    Route::put('/api/v1/nodelists', 'bld\ddosspelbord\classes\api\v1\NodesController@putNodes');
    Route::get('/api/v1/nodelists', 'bld\ddosspelbord\classes\api\v1\NodesController@getAllNodes');
    Route::get('/api/v1/nodelists/{id}', 'bld\ddosspelbord\classes\api\v1\NodesController@getNodesByID');

    Route::get('/api/v1/targets', 'bld\ddosspelbord\classes\api\v1\TargetsController@getTargets');
    Route::post('/api/v1/targets/{targetId}/state/{state}', 'bld\ddosspelbord\classes\api\v1\TargetsController@PostStatusTarget');

    Route::get('/api/v1/test', function () {
        return response()->json([
                                    'error' => 'Top Secret information only accessible using an access token',
                                ], 200);
    });
});

// This will allow user to login and receive token
Route::post('api/authentication', 'bld\ddosspelbord\classes\api\authentication\LoginController@login');

// This will allow user with expired access token to get a new one
Route::post('api/authentication/refresh', 'bld\ddosspelbord\classes\api\authentication\LoginController@refreshToken');






/**
 * Laravel used passport Routes
 */

Route::group([
                 'as' => 'passport.',
                 'prefix' => 'oauth',
                 'namespace' => '\Laravel\Passport\Http\Controllers',
             ], function () {

    Route::post('/token', [
        'uses' => 'AccessTokenController@issueToken',
        'as' => 'token',
        'middleware' => 'throttle',
    ]);

    Route::get('/authorize', [
        'uses' => 'AuthorizationController@authorize',
        'as' => 'authorizations.authorize',
        'middleware' => 'web',
    ]);
});
