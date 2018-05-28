<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('links', array('page_title' => 'All links'));
});

Route::get('/more-functions', function()
{
	return View::make('links-more-functions', array('page_title' => 'All links'));
});

Route::get('/bonanza/get-link-spied-booth', array('as' => 'bonanza.getLinkSpiedBooth', 'uses' => 'BonanzaController@getLinkSpiedBooth'));
Route::post('/bonanza/post-link-spied-booth', array('as' => 'bonanza.postLinkSpiedBooth', 'uses' => 'BonanzaController@postLinkSpiedBooth'));


Route::get('/bonanza/get-data-and-export-csv', array('as' => 'bonanza.getDataAndExportToCSV', 'uses' => 'BonanzaController@getDataAndExportToCSV'));
Route::post('/bonanza/post-data-and-export-csv', array('as' => 'bonanza.postDataAndExportToCSV', 'uses' => 'BonanzaController@postDataAndExportToCSV'));


Route::get('/bonanza/get-items-by-keyword', array('as' => 'bonanza.getItemByKeyWord', 'uses' => 'BonanzaController@getItemByKeyWord'));
Route::post('/bonanza/post-items-by-keyword', array('as' => 'bonanza.postItemByKeyWord', 'uses' => 'BonanzaController@postItemByKeyWord'));
