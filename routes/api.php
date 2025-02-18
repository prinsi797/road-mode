<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [ApiController::class, 'login']);
Route::post('register', [ApiController::class, 'register']);

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::get('logout', [ApiController::class, 'logout']);
    Route::get('get-service', [ApiController::class, 'getService']);
    Route::get('/get-companies-for-vehicle', [ApiController::class, 'getCompaniesForVehicle']);
    Route::get('/get-vehicle-models', [ApiController::class, 'getVehicleModels']);
    Route::get('/services', [ApiController::class, 'getServicesByVehicle']);
    Route::get('/branches', [ApiController::class, 'getBranches']);

    // service api
    Route::get('/service-category', [ApiController::class, 'serviceCategory']);
    Route::post('/create-service', [ApiController::class, 'createService']);
    Route::post('/services-update/{id}', [ApiController::class, 'updateService']);
    Route::delete('/services-delete/{id}', [ApiController::class, 'deleteService']);

    // city master
    Route::get('/city', [ApiController::class, 'city']);
    Route::post('/create-city', [ApiController::class, 'createCity']);
    Route::post('/city-update/{id}', [ApiController::class, 'updateCity']);
    Route::delete('/city-delete/{id}', [ApiController::class, 'deleteCity']);

    //user Master
    Route::get('/user', [ApiController::class, 'User']);
    Route::post('/create-user', [ApiController::class, 'createUser']);
    Route::post('/user-update/{id}', [ApiController::class, 'updateUser']);
    Route::delete('/user-delete/{id}', [ApiController::class, 'userDelete']);

    //role Master
    Route::post('/create-role', [ApiController::class, 'createRole']);
    Route::get('/roles', [ApiController::class, 'getRoles']);
    Route::post('/update-roles/{id}', [ApiController::class, 'updateRole']);
});
