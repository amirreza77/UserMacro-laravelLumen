<?php
  
 
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\UserController;
/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/',  function () {
    //
});
 $router->get('send_email' ,'UserController@mail');
$router->group(['prefix' => 'user'], function () use ($router) {
    Route::post('add','UserController@store');
    Route::post('validatebycell','UserController@validateByCell');
    Route::post('validatecode','UserController@validateCode');
    Route::post('userlogin','UserController@userLogin');
    Route::post('edit','UserController@edit');
    Route::post('show','UserController@show');
    Route::post('lwup','UserController@verifyEmailByUserAndPass');
    Route::get('verifyemail','UserController@verifyEmail');
    
 
    
    
    
});