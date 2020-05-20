<?php

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

use Illuminate\Support\Str;
use TCG\Voyager\Events\Routing;
use TCG\Voyager\Events\RoutingAdmin;
use TCG\Voyager\Events\RoutingAdminAfter;
use TCG\Voyager\Events\RoutingAfter;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\Module;
use TCG\Voyager\Models\ModuleMember;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');



Route::middleware(['auth'])->prefix('api/')->namespace('Api')->group(function() {        
    
    try {
        foreach (Voyager::model('DataType')::all() as $dataType) {
            $breadController = 'VoyagerBreadApiController';

             Route::resource($dataType->slug, $breadController);
        }
    } catch (\InvalidArgumentException $e) {
        throw new \InvalidArgumentException("Custom routes hasn't been configured because: ".$e->getMessage(), 1);
    } catch (\Exception $e) {
        // do nothing, might just be because table not yet migrated.
    }

});

Route::middleware(['auth'])->prefix('admin/')->namespace('Report')->group(function() {        
    
    try {
        foreach (ModuleMember::where('main', false)->with('dataTypes', 'module')->get() as $dataType) {
            $breadController = 'ReportController';

             Route::resource($dataType->slug, $breadController);
             $module = $dataType->module->slug;    
            
             Route::post('/report/{k}',['as' => 'report.filter','uses' => 'ReportController@filter']);
         
        }
    } catch (\InvalidArgumentException $e) {
        throw new \InvalidArgumentException("Custom routes hasn't been configured because: ".$e->getMessage(), 1);
    } catch (\Exception $e) {
        // do nothing, might just be because table not yet migrated.
    }

});

Route::middleware(['auth'])->prefix('admin/')->namespace('Module')->group(function() {
           
    try {
        foreach (ModuleMember::where('main', false)->with('dataTypes', 'module')->get() as $dataType) {           
                      
            $breadController = 'ModuleController';

            $module_members = $dataType->dataTypes->slug;         
            $module = $dataType->module->slug;    

            Route::resource($module_members, $breadController);      
            
            Route::get($module.'/{k}/{w}/status/', ['uses' => 'ModuleController@status', 'as' => 'module.'.$module.'.status']);          
            Route::get($module.'/{k}/{w}/delete/'.$module_members, ['uses' => 'ModuleController@editModule', 'as' => 'module.'.$module_members.'.delete']);
            Route::get($module.'/{k}/{w}/show/'.$module_members, ['uses' => 'ModuleController@editModule', 'as' => 'module.'.$module_members.'.show']);
            Route::get($module.'/{k}/{w}/edit/'.$module_members, ['uses' => 'ModuleController@editModule', 'as' => 'module.'.$module_members.'.edit']);
            Route::get($module.'/{k}/{w}/create/'.$module_members, ['uses' => 'ModuleController@createModule', 'as' => 'module.'.$module_members.'.create']);
            Route::put($module.'/{k}/{w}/update/'.$module_members, ['uses' => 'ModuleController@updateModule', 'as' => 'module.'.$module_members.'.update']);
            Route::delete($module.'/{k}/{w}/delete/'.$module_members, ['uses' => 'ModuleController@destroyModule', 'as' => 'module.'.$module_members.'.delete']);
            Route::post($module.'/store/{k}/'.$module_members, ['uses' => 'ModuleController@storeModule', 'as' => 'module.'.$module_members.'.store']);
  
        }
    } catch (\InvalidArgumentException $e) {
        throw new \InvalidArgumentException("Custom routes hasn't been configured because: ".$e->getMessage(), 1);
    } catch (\Exception $e) {
        // do nothing, might just be because table not yet migrated.
    }
});


Route::group(['prefix' => 'admin'], function () {
    
    Route::group(['as' => 'voyager.'], function () {
        event(new Routing());
    
        //$namespacePrefix = '\\'.config('voyager.controllers.namespace').'\\';
        $namespacePrefix = 'Voyager\\';
    
        Route::get('login', ['uses' => $namespacePrefix.'VoyagerAuthController@login',     'as' => 'login']);
        Route::post('login', ['uses' => $namespacePrefix.'VoyagerAuthController@postLogin', 'as' => 'postlogin']);
    
        Route::group(['middleware' => 'admin.user'], function () use ($namespacePrefix) {
            event(new RoutingAdmin());
    
            // Main Admin and Logout Route
            Route::get('/', ['uses' => $namespacePrefix.'VoyagerController@index',   'as' => 'dashboard']);
            Route::post('logout', ['uses' => $namespacePrefix.'VoyagerController@logout',  'as' => 'logout']);
            Route::post('upload', ['uses' => $namespacePrefix.'VoyagerController@upload',  'as' => 'upload']);
    
            Route::get('profile', ['uses' => $namespacePrefix.'VoyagerUserController@profile', 'as' => 'profile']);
    
            try {
                foreach (Voyager::model('DataType')::all() as $dataType) {
                    $breadController = $dataType->controller
                                     ? Str::start($dataType->controller, '\\')
                                     : $namespacePrefix.'VoyagerBaseController';
    
                    Route::get($dataType->slug.'/order', $breadController.'@order')->name($dataType->slug.'.order');
                    Route::post($dataType->slug.'/action', $breadController.'@action')->name($dataType->slug.'.action');
                    Route::post($dataType->slug.'/order', $breadController.'@update_order')->name($dataType->slug.'.order');
                    Route::get($dataType->slug.'/{id}/restore', $breadController.'@restore')->name($dataType->slug.'.restore');
                    Route::get($dataType->slug.'/relation', $breadController.'@relation')->name($dataType->slug.'.relation');
                    Route::resource($dataType->slug, $breadController);
                }
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException("Custom routes hasn't been configured because: ".$e->getMessage(), 1);
            } catch (\Exception $e) {
                // do nothing, might just be because table not yet migrated.
            }
    
            // Role Routes
            Route::resource('roles', $namespacePrefix.'VoyagerRoleController');
    
            // Menu Routes
            Route::group([
                'as'     => 'menus.',
                'prefix' => 'menus/{menu}',
            ], function () use ($namespacePrefix) {
                Route::get('builder', ['uses' => $namespacePrefix.'VoyagerMenuController@builder',    'as' => 'builder']);
                Route::post('order', ['uses' => $namespacePrefix.'VoyagerMenuController@order_item', 'as' => 'order']);
    
                Route::group([
                    'as'     => 'item.',
                    'prefix' => 'item',
                ], function () use ($namespacePrefix) {
                    Route::delete('{id}', ['uses' => $namespacePrefix.'VoyagerMenuController@delete_menu', 'as' => 'destroy']);
                    Route::post('/', ['uses' => $namespacePrefix.'VoyagerMenuController@add_item',    'as' => 'add']);
                    Route::put('/', ['uses' => $namespacePrefix.'VoyagerMenuController@update_item', 'as' => 'update']);
                });
            });
    
            // Settings
            Route::group([
                'as'     => 'settings.',
                'prefix' => 'settings',
            ], function () use ($namespacePrefix) {
                Route::get('/', ['uses' => $namespacePrefix.'VoyagerSettingsController@index',        'as' => 'index']);
                Route::post('/', ['uses' => $namespacePrefix.'VoyagerSettingsController@store',        'as' => 'store']);
                Route::put('/', ['uses' => $namespacePrefix.'VoyagerSettingsController@update',       'as' => 'update']);
                Route::delete('{id}', ['uses' => $namespacePrefix.'VoyagerSettingsController@delete',       'as' => 'delete']);
                Route::get('{id}/move_up', ['uses' => $namespacePrefix.'VoyagerSettingsController@move_up',      'as' => 'move_up']);
                Route::get('{id}/move_down', ['uses' => $namespacePrefix.'VoyagerSettingsController@move_down',    'as' => 'move_down']);
                Route::put('{id}/delete_value', ['uses' => $namespacePrefix.'VoyagerSettingsController@delete_value', 'as' => 'delete_value']);
            });
    
            // Admin Media
            Route::group([
                'as'     => 'media.',
                'prefix' => 'media',
            ], function () use ($namespacePrefix) {
                Route::get('/', ['uses' => $namespacePrefix.'VoyagerMediaController@index',              'as' => 'index']);
                Route::post('files', ['uses' => $namespacePrefix.'VoyagerMediaController@files',              'as' => 'files']);
                Route::post('new_folder', ['uses' => $namespacePrefix.'VoyagerMediaController@new_folder',         'as' => 'new_folder']);
                Route::post('delete_file_folder', ['uses' => $namespacePrefix.'VoyagerMediaController@delete', 'as' => 'delete']);
                Route::post('move_file', ['uses' => $namespacePrefix.'VoyagerMediaController@move',          'as' => 'move']);
                Route::post('rename_file', ['uses' => $namespacePrefix.'VoyagerMediaController@rename',        'as' => 'rename']);
                Route::post('upload', ['uses' => $namespacePrefix.'VoyagerMediaController@upload',             'as' => 'upload']);
                Route::post('remove', ['uses' => $namespacePrefix.'VoyagerMediaController@remove',             'as' => 'remove']);
                Route::post('crop', ['uses' => $namespacePrefix.'VoyagerMediaController@crop',             'as' => 'crop']);
            });
    
            // BREAD Routes
            Route::group([
                'as'     => 'bread.',
                'prefix' => 'bread',
            ], function () use ($namespacePrefix) {
                Route::get('/', ['uses' => $namespacePrefix.'VoyagerBreadController@index',              'as' => 'index']);
                Route::get('{table}/create', ['uses' => $namespacePrefix.'VoyagerBreadController@create',     'as' => 'create']);
                Route::post('/', ['uses' => $namespacePrefix.'VoyagerBreadController@store',   'as' => 'store']);
                Route::get('{table}/edit', ['uses' => $namespacePrefix.'VoyagerBreadController@edit', 'as' => 'edit']);
                Route::put('{id}', ['uses' => $namespacePrefix.'VoyagerBreadController@update',  'as' => 'update']);
                Route::delete('{id}', ['uses' => $namespacePrefix.'VoyagerBreadController@destroy',  'as' => 'delete']);
                Route::post('relationship', ['uses' => $namespacePrefix.'VoyagerBreadController@addRelationship',  'as' => 'relationship']);
                Route::get('delete_relationship/{id}', ['uses' => $namespacePrefix.'VoyagerBreadController@deleteRelationship',  'as' => 'delete_relationship']);
            });
    
            // Database Routes
            Route::resource('database', $namespacePrefix.'VoyagerDatabaseController');
    
            // Compass Routes
            Route::group([
                'as'     => 'compass.',
                'prefix' => 'compass',
            ], function () use ($namespacePrefix) {
                Route::get('/', ['uses' => $namespacePrefix.'VoyagerCompassController@index',  'as' => 'index']);
                Route::post('/', ['uses' => $namespacePrefix.'VoyagerCompassController@index',  'as' => 'post']);
            });
    
            event(new RoutingAdminAfter());
        });
    
        //Asset Routes
        Route::get('assets/{path}', ['uses' => $namespacePrefix.'VoyagerController@assets', 'as' => 'assets'])->where('path', '(.*)');
    
        event(new RoutingAfter());
    });
    

});