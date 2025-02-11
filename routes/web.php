<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// frontend routes

Route::get('/', 'Frontend\HomeController@index')->name('main');
Route::get('/about', 'Frontend\HomeController@about')->name('about');
Route::get('/service', 'Frontend\HomeController@service')->name('service');
Route::get('/booking', 'Frontend\HomeController@booking')->name('booking');
Route::get('/contact', 'Frontend\HomeController@contact')->name('contact');
Route::get('/testimonial', 'Frontend\HomeController@testimonial')->name('testimonial');
Route::get('/team', 'Frontend\HomeController@team')->name('team');


// end


Route::get('/admin', 'IndexController@index')->name('home');

Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => true, // Password Reset Routes...
    'verify' => true, // Email Verification Routes...
]);

Route::get('verify/resend', 'Auth\TwoFactorController@resend')->name('verify.resend');
Route::resource('verify', 'Auth\TwoFactorController')->only(['index', 'store']);

Route::middleware(['auth', 'twofactor'])->prefix('admin')->group(function () {
    // Test Route
    Route::get('test', 'Admin\TestController@test')->name('admin.test');

    // For Dashboard
    Route::get('dashboard', 'Admin\HomeController@index')->name('admin.dashboard');

    // For Module
    Route::get('modules', 'Admin\ModuleController@index')->name('admin.modules.index');
    Route::get('modules/add', 'Admin\ModuleController@create')->name('admin.modules.create');
    Route::get('modules/edit/{encrypted_id}', 'Admin\ModuleController@edit')->name('admin.modules.edit');
    Route::get('modules/show/{encrypted_id}', 'Admin\ModuleController@show')->name('admin.modules.show');
    Route::post('modules/store', 'Admin\ModuleController@store')->name('admin.modules.store');
    Route::post('modules/update', 'Admin\ModuleController@update')->name('admin.modules.update');
    Route::get('modules/ajax', 'Admin\ModuleController@ajax')->name('admin.modules.ajax');
    Route::post('modules/delete', 'Admin\ModuleController@delete')->name('admin.modules.delete');

    // For Users
    Route::get('users', 'Admin\UserController@index')->name('admin.users.index');
    Route::get('users/add', 'Admin\UserController@create')->name('admin.users.create');
    Route::get('users/edit/{encrypted_id}', 'Admin\UserController@edit')->name('admin.users.edit');
    Route::get('users/show/{encrypted_id}', 'Admin\UserController@show')->name('admin.users.show');
    Route::post('users/store', 'Admin\UserController@store')->name('admin.users.store');
    Route::post('users/update', 'Admin\UserController@update')->name('admin.users.update');
    Route::get('users/ajax', 'Admin\UserController@ajax')->name('admin.users.ajax');
    Route::post('users/delete', 'Admin\UserController@delete')->name('admin.users.delete');

    //For Products
    Route::get('products', 'Admin\ProductController@index')->name('admin.category_products.index');
    Route::get('products/add', 'Admin\ProductController@create')->name('admin.category_products.create');
    Route::get('products/edit/{encrypted_id}', 'Admin\ProductController@edit')->name('admin.category_products.edit');
    Route::get('products/show/{encrypted_id}', 'Admin\ProductController@show')->name('admin.category_products.show');
    Route::post('products/store', 'Admin\ProductController@store')->name('admin.category_products.store');
    Route::post('products/update', 'Admin\ProductController@update')->name('admin.category_products.update');
    Route::get('products/ajax', 'Admin\ProductController@ajax')->name('admin.category_products.ajax');
    Route::post('products/delete', 'Admin\ProductController@delete')->name('admin.category_products.delete');
    //  service_categories

    Route::get('categories', 'Admin\ServiceCategoryController@index')->name('admin.service_categories.index');
    Route::get('categories/add', 'Admin\ServiceCategoryController@create')->name('admin.service_categories.create');
    Route::get('categories/edit/{encrypted_id}', 'Admin\ServiceCategoryController@edit')->name('admin.service_categories.edit');
    Route::get('categories/show/{encrypted_id}', 'Admin\ServiceCategoryController@show')->name('admin.service_categories.show');
    Route::post('categories/store', 'Admin\ServiceCategoryController@store')->name('admin.service_categories.store');
    Route::post('categories/update', 'Admin\ServiceCategoryController@update')->name('admin.service_categories.update');
    Route::get('categories/ajax', 'Admin\ServiceCategoryController@ajax')->name('admin.service_categories.ajax');
    Route::post('categories/delete', 'Admin\ServiceCategoryController@delete')->name('admin.service_categories.delete');

    // city master
    Route::get('city', 'Admin\CityMasterController@index')->name('admin.city_master.index');
    Route::get('city/add', 'Admin\CityMasterController@create')->name('admin.city_master.create');
    Route::get('city/edit/{encrypted_id}', 'Admin\CityMasterController@edit')->name('admin.city_master.edit');
    Route::get('city/show/{encrypted_id}', 'Admin\CityMasterController@show')->name('admin.city_master.show');
    Route::post('city/store', 'Admin\CityMasterController@store')->name('admin.city_master.store');
    Route::post('city/update', 'Admin\CityMasterController@update')->name('admin.city_master.update');
    Route::get('city/ajax', 'Admin\CityMasterController@ajax')->name('admin.city_master.ajax');
    Route::post('city/delete', 'Admin\CityMasterController@delete')->name('admin.city_master.delete');

    //area master

    Route::get('area', 'Admin\AreaMasterController@index')->name('admin.area_master.index');
    Route::get('area/add', 'Admin\AreaMasterController@create')->name('admin.area_master.create');
    Route::get('area/edit/{encrypted_id}', 'Admin\AreaMasterController@edit')->name('admin.area_master.edit');
    Route::get('area/show/{encrypted_id}', 'Admin\AreaMasterController@show')->name('admin.area_master.show');
    Route::post('area/store', 'Admin\AreaMasterController@store')->name('admin.area_master.store');
    Route::post('area/update', 'Admin\AreaMasterController@update')->name('admin.area_master.update');
    Route::get('area/ajax', 'Admin\AreaMasterController@ajax')->name('admin.area_master.ajax');
    Route::post('area/delete', 'Admin\AreaMasterController@delete')->name('admin.area_master.delete');

    // branch master
    Route::get('branch', 'Admin\BranchMasterController@index')->name('admin.branch_master.index');
    Route::get('branch/add', 'Admin\BranchMasterController@create')->name('admin.branch_master.create');
    Route::get('branch/edit/{encrypted_id}', 'Admin\BranchMasterController@edit')->name('admin.branch_master.edit');
    Route::get('branch/show/{encrypted_id}', 'Admin\BranchMasterController@show')->name('admin.branch_master.show');
    Route::post('branch/store', 'Admin\BranchMasterController@store')->name('admin.branch_master.store');
    Route::post('branch/update', 'Admin\BranchMasterController@update')->name('admin.branch_master.update');
    Route::get('branch/ajax', 'Admin\BranchMasterController@ajax')->name('admin.branch_master.ajax');
    Route::post('branch/delete', 'Admin\BranchMasterController@delete')->name('admin.branch_master.delete');
    // Company Master
    Route::get('company', 'Admin\CompanyMasterController@index')->name('admin.company_master.index');
    Route::get('company/add', 'Admin\CompanyMasterController@create')->name('admin.company_master.create');
    Route::get('company/edit/{encrypted_id}', 'Admin\CompanyMasterController@edit')->name('admin.company_master.edit');
    Route::get('company/show/{encrypted_id}', 'Admin\CompanyMasterController@show')->name('admin.company_master.show');
    Route::post('company/store', 'Admin\CompanyMasterController@store')->name('admin.company_master.store');
    Route::post('company/update', 'Admin\CompanyMasterController@update')->name('admin.company_master.update');
    Route::get('company/ajax', 'Admin\CompanyMasterController@ajax')->name('admin.company_master.ajax');
    Route::post('company/delete', 'Admin\CompanyMasterController@delete')->name('admin.company_master.delete');

    //Model Master

    Route::get('model', 'Admin\ModelMasterController@index')->name('admin.model_master.index');
    Route::get('model/add', 'Admin\ModelMasterController@create')->name('admin.model_master.create');
    Route::get('model/edit/{encrypted_id}', 'Admin\ModelMasterController@edit')->name('admin.model_master.edit');
    Route::get('model/show/{encrypted_id}', 'Admin\ModelMasterController@show')->name('admin.model_master.show');
    Route::post('model/store', 'Admin\ModelMasterController@store')->name('admin.model_master.store');
    Route::post('model/update', 'Admin\ModelMasterController@update')->name('admin.model_master.update');
    Route::get('model/ajax', 'Admin\ModelMasterController@ajax')->name('admin.model_master.ajax');
    Route::post('model/delete', 'Admin\ModelMasterController@delete')->name('admin.model_master.delete');


    //Photo Gallery Master

    Route::get('gallery', 'Admin\PhotoGalleryController@index')->name('admin.photo_gallary_master.index');
    Route::get('gallery/add', 'Admin\PhotoGalleryController@create')->name('admin.photo_gallary_master.create');
    Route::get('gallery/edit/{encrypted_id}', 'Admin\PhotoGalleryController@edit')->name('admin.photo_gallary_master.edit');
    Route::get('gallery/show/{encrypted_id}', 'Admin\PhotoGalleryController@show')->name('admin.photo_gallary_master.show');
    Route::post('gallery/store', 'Admin\PhotoGalleryController@store')->name('admin.photo_gallary_master.store');
    Route::post('gallery/update', 'Admin\PhotoGalleryController@update')->name('admin.photo_gallary_master.update');
    Route::get('gallery/ajax', 'Admin\PhotoGalleryController@ajax')->name('admin.photo_gallary_master.ajax');
    Route::post('gallery/delete', 'Admin\PhotoGalleryController@delete')->name('admin.photo_gallary_master.delete');


    // For Roles
    Route::get('roles', 'Admin\RoleController@index')->name('admin.roles.index');
    Route::get('roles/add', 'Admin\RoleController@create')->name('admin.roles.create');
    Route::get('roles/edit', 'Admin\RoleController@edit')->name('admin.roles.edit');
    Route::get('roles/show/{encrypted_id}', 'Admin\RoleController@show')->name('admin.roles.show');
    Route::post('roles/store', 'Admin\RoleController@store')->name('admin.roles.store');
    Route::post('roles/update', 'Admin\RoleController@update')->name('admin.roles.update');
    Route::get('roles/ajax', 'Admin\RoleController@ajax')->name('admin.roles.ajax');
    Route::post('roles/delete', 'Admin\RoleController@delete')->name('admin.roles.delete');

    // For Settings
    Route::get('settings', 'Admin\SettingController@index')->name('admin.settings.index');
    Route::get('settings/add', 'Admin\SettingController@create')->name('admin.settings.create');
    Route::get('settings/edit/{encrypted_id}', 'Admin\SettingController@edit')->name('admin.settings.edit');
    Route::get('settings/show/{encrypted_id}', 'Admin\SettingController@show')->name('admin.settings.show');
    Route::get('settings/edit_profile', 'Admin\SettingController@edit_profile')->name('admin.settings.edit_profile');
    Route::post('settings/store', 'Admin\SettingController@store')->name('admin.settings.store');
    Route::post('settings/update', 'Admin\SettingController@update')->name('admin.settings.update');
    Route::get('settings/ajax', 'Admin\SettingController@ajax')->name('admin.settings.ajax');
    Route::post('settings/delete', 'Admin\SettingController@delete')->name('admin.settings.delete');

    // For Pages
    Route::get('pages', 'Admin\PageController@index')->name('admin.pages.index');
    Route::get('pages/add', 'Admin\PageController@create')->name('admin.pages.create');
    Route::get('pages/edit/{encrypted_id}', 'Admin\PageController@edit')->name('admin.pages.edit');
    Route::get('pages/show/{encrypted_id}', 'Admin\PageController@show')->name('admin.pages.show');
    Route::post('pages/store', 'Admin\PageController@store')->name('admin.pages.store');
    Route::post('pages/update', 'Admin\PageController@update')->name('admin.pages.update');
    Route::get('pages/ajax', 'Admin\PageController@ajax')->name('admin.pages.ajax');
    Route::post('pages/delete', 'Admin\PageController@delete')->name('admin.pages.delete');
});