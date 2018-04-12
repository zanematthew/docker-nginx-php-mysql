<?php

use Illuminate\Http\Request;

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

/*
|--------------------------------------------------------------------------
| User managed content routes
|--------------------------------------------------------------------------
*/
Route::group([
    'prefix'     => 'user',
    'middleware' => 'auth:api',
    ], function () {

    Route::get('/', function (Request $request) {
        return $request->user();
    });

    Route::get('/schedule/events/{id}/' , 'ScheduleController@events')->name('user.schedule.events');
    Route::get('/schedule'              , 'ScheduleController@index')->name('user.schedule.index');

    Route::post('/schedule/toggle/{eventId}/to/{scheudleId}' , 'ScheduleController@toggleEventTo')->name('user.schedule.event.toggle');
    Route::post('/schedule/{id}/update/'                     , 'ScheduleController@update')->name('user.schedule.update');
    Route::post('/schedule/new/'                             , 'ScheduleController@store')->name('user.schedule.store');

    Route::delete('/schedule/{id}/delete/', 'ScheduleController@delete')->name('user.schedule.delete');

    /*
    |--------------------------------------------------------------------------
    | Library API Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/library/toggle/{id}/{type}/' , 'LibraryController@toggle')->name('library.toggle.item');
    Route::get('/library/'                     , 'LibraryController@index')->name('library.get.items');
});

/*
|--------------------------------------------------------------------------
| Venue(s) routes
|--------------------------------------------------------------------------
*/
Route::group([
    'prefix'     => 'venues',
    'middleware' => 'auth:api',
    ], function () {
    Route::get('/events/{id}'  , 'VenueController@events')->name('venue.events');
    Route::get('/{state}'      , 'VenueController@state')->name('venues.state');
    Route::get('/'             , 'VenueController@index')->name('venues');
});

Route::group([
    'prefix'     => 'venue',
    'middleware' => 'auth:api',
    ], function() {
    Route::get('/{id}/{slug?}' , 'VenueController@single')->name('venue.single');
});

/*
|--------------------------------------------------------------------------
| Events Routes -- Plural
|--------------------------------------------------------------------------
|
| All events routes are prefixed with [/api/]events. URL constraints are
| located in `RouteServiceProvider.php`.
|
| events/, state/, and type/ accept a boolean query parameter of;
| this_month: All events from today until the end of the month
| next_month: All events next month
| upcoming: All events from today into the future
*/
Route::group([
    'prefix'     => 'events',
    'middleware' => 'auth:api',
    ], function () {

    Route::get('/{state}' , 'EventController@state')->name('events.state');
    Route::get('/{type}'  , 'EventController@type')->name('events.type');

    Route::get('/{year}/{month}/{type}' , 'EventController@yearMonthType')->name('events.year.month.type');
    Route::get('/{year}/{month}'        , 'EventController@yearMonth')->name('events.year.month');
    Route::get('/{year}/{type}'         , 'EventController@yearType')->name('events.year.type');
    Route::get('/{year}'                , 'EventController@year')->name('events.year');
    Route::get('/{year}/{month}/{type}/{state}' , 'EventController@yearMonthTypeState')->name('events.year.month.type.state');
    Route::get('/{year}/{month}/{state}'        , 'EventController@yearMonthState')->name('events.year.month.state');
    Route::get('/{year}/{type}/{state}'         , 'EventController@yearTypeState')->name('events.year.type.state');
    Route::get('/{year}/{state}'                , 'EventController@yearState')->name('events.year.state');

    Route::get('/', 'EventController@index')->name('events.index');
});

/*
|--------------------------------------------------------------------------
| Event Routes -- Singular
|--------------------------------------------------------------------------
|
*/
Route::group([
    'prefix'     => 'event',
    'middleware' => 'auth:api',
    ], function() {
    Route::get('/{id}/{slug?}'          , 'EventController@single')->name('event.single');
});

Route::get('/states', function () {
    return response()->json(\App\State::select('name','abbr')->orderBy('name')->get());
})->middleware('auth:api');

Route::group([
    'prefix'     => 'search',
    'middleware' => 'auth:api',
    ], function() {
        Route::get('/event/phrase', 'SearchEventController@phrase')->name('search.event.phrase');
        Route::get('/event/date/', 'SearchEventController@date')->name('search.event.date');
        Route::get('/event/suggestion/', 'SearchEventController@suggestion')->name('search.event.suggestion');

        Route::get('/venue/phrase', 'SearchVenueController@phrase')->name('search.venue.phrase');
        Route::get('/venue/suggestion/', 'SearchVenueController@suggestion')->name('search.venue.suggestion');

        Route::get('/place/', 'SearchPlaceController@index')->name('search.index');
    }
);