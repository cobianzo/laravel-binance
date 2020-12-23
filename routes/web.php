<?php

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// TODO: Create controller for page.
// only authenticated users
Route::group( ['middleware' => 'auth'], function() {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/profile', [App\Http\Controllers\UserController::class, 'edit',])->name('profile');
    
    
    Route::resource('users', App\Http\Controllers\UserController::class);
    
    // practicing using forms for sending data to the DB & populating form fields with DB data
    // Route::get('profile', 'ProfileController@index');
    // Route::patch('profile/{id}', 'ProfileController@update');
    Route::post('/place-buy', [App\Http\Controllers\MyBinanceController::class, 'place_buy',]);
    Route::post('/place-oco', [App\Http\Controllers\MyBinanceController::class, 'place_oco',]);
    Route::post('/cancel-order', [App\Http\Controllers\MyBinanceController::class, 'cancel_open_order',]);

});


// For vuejs.
Route::get('buy', [App\Http\Controllers\UserController::class, 'buy'])->name('buy');
//Route::get('/profile', [App\Http\Controllers\HomeController::class, 'index'])->name('edit-profile');


Route::post('test', [App\Http\Controllers\MyBinanceController::class, 'test']);

// Route::get('binance-balance', function()
// {
//     $html = view('components/binance-balance', [])->render();
//     return $html;
// });
// Reload templates as partials with ajax
// Route::get('binance-balance', [App\Http\Controllers\MyBinanceController::class, 'load_partial']);
Route::get('load-partial-ajax', function() {
        // mandatory $_REQUEST['template'] with the mane of the component
        // ie. $_REQUEST['template']=binance-trades for  <x-binance-trades>
        return View::make('partial-load-template-ajax', $_REQUEST); 
});