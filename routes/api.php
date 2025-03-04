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
    Route::get('/get-service', [ApiController::class, 'getService']);
    Route::get('/get-companies-for-vehicle', [ApiController::class, 'getCompaniesForVehicle']);
    Route::get('/get-vehicle-models', [ApiController::class, 'getVehicleModels']);
    Route::get('/services', [ApiController::class, 'getServicesByVehicle']);
    Route::get('/branches', [ApiController::class, 'getBranches']);

    // service api
    Route::get('/service-category', [ApiController::class, 'serviceCategory']);
    Route::post('/create-service', [ApiController::class, 'createService']);
    Route::post('/services-update', [ApiController::class, 'updateService']);
    Route::delete('/services-delete', [ApiController::class, 'deleteService']);

    // city master
    Route::get('/city', [ApiController::class, 'city']);
    Route::post('/create-city', [ApiController::class, 'createCity']);
    Route::post('/city-update', [ApiController::class, 'updateCity']);
    Route::delete('/city-delete', [ApiController::class, 'deleteCity']);

    //user Master
    Route::get('/user', [ApiController::class, 'User']);
    Route::post('/create-user', [ApiController::class, 'createUser']);
    Route::post('/user-update', [ApiController::class, 'updateUser']);
    Route::delete('/user-delete', [ApiController::class, 'userDelete']);

    //role Master
    Route::post('/create-role', [ApiController::class, 'createRole']);
    Route::get('/roles', [ApiController::class, 'getRoles']);
    Route::post('/update-roles/{id}', [ApiController::class, 'updateRole']);

    //Area master
    Route::get('/area', [ApiController::class, 'Area']);
    Route::post('/create-area', [ApiController::class, 'createArea']);
    Route::post('/area-update', [ApiController::class, 'updateArea']);
    Route::delete('/area-delete', [ApiController::class, 'areaDelete']);

    // packge
    Route::get('/packages', [ApiController::class, 'getPackages']);
    Route::post('/add-pickup-inquiry', [ApiController::class, 'addPickupInquiry']);

    Route::get('/customer-get', [ApiController::class, 'getAuthenticatedCustomer']);
    //branch
    Route::get('/branch', [ApiController::class, 'getBranch']);
    Route::post('/branch-create', [ApiController::class, 'createBranch']);
    Route::post('/branch-update', [ApiController::class, 'updateBranch']);
    Route::delete('/branch-delete', [ApiController::class, 'branchDelete']);

    Route::get('/get-my-request', [ApiController::class, 'getMyRequest']);

    Route::post('/update-company', [ApiController::class, 'updateCompany']);
    Route::delete('/delete-company', [ApiController::class, 'deleteCompany']);

    //model master
    Route::post('/create-model', [ApiController::class, 'createModel']);

    //create package
    Route::post('/create-package', [ApiController::class, 'createPackage']);
    Route::post('/update-package', [ApiController::class, 'updatePackage']);
    Route::delete('/delete-package', [ApiController::class, 'deletePackage']);
});
Route::post('/cutomer-register', [ApiController::class, 'customerRegister']);
Route::post('/customer-login', [ApiController::class, 'customerLogin']);

//without token
Route::get('/company', [ApiController::class, 'Company']);

Route::get('/get-branch', [ApiController::class, 'Branch']);
Route::get('/get-packages', [ApiController::class, 'Packages']);
Route::get('/get-company', [ApiController::class, 'Companies']);
Route::get('/get-model', [ApiController::class, 'Model']);
