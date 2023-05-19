<?php

use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

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

    $html = "<h1 style='color:red'>Aliens</h1>";

    return view('welcome', [
        'html' => $html
    ]);
});

Route::get('/date' , function(){
    $date = Carbon::now();
    $date->locale('nl_BE'); // Set the locale to Dutch (Belgium)

    echo $date->isoFormat('LL');
    echo $date->isoFormat('LLLL');
    echo "<hr>";
    echo $date->toDateString();
});


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
    // not secure... not adding any new routes
});

// default a route login and redirect to admin/login
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');


// create export csv routes that are protected by auth middlewar
Route::get('/export/csv', function(){
    // get all aliens and eager load the abilities
    $aliens = App\Models\Alien::with('abilities')->get();
    // foreach the aliens into a csv file without using a third party package
    $csvEport = "";
    foreach ($aliens as $alien) {
        // export the alien name, email and location and implode the abilities
        $csvEport .= $alien->name . ",".$alien->email.",".$alien->location.",".implode(",",$alien->abilities->pluck('name')->toArray())."\n";
        // $csvEport .= $alien->name . ",".$alien->email.",".$alien->location."\n";
    }
    // return the csv file
    return response($csvEport, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="aliensExport.csv"'
    ]);
})->middleware('auth')->name('export');

// alternative using laracsv package

Route::get('/export/csv2', function(){
    $aliens = App\Models\Alien::with('abilities')->get();
    $csvExporter = new \Laracsv\Export();
    $csvExporter->build($aliens, ['email', 'location'])->download();
})->middleware('auth')->name('export2');




// second way to protect routes with auth middleware using a group function
/* Route::middleware('auth')->group(function () {
    Route::get('/test', function(){
        return "test";
    });
   
    Route::get('/export/csv', function(){
        return "export aliens";
    });

    Route::get('/export/pdf', function(){
        return "export aliens";
    });
}); */