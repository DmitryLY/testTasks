<?php

use Illuminate\Support\Facades\Route;
use App\TestTasks\TestTasks;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/{vue}', function () {
    return view('test_tasks');
})->where('vue', '(?!api).*');

Route::match(['GET', 'POST', 'PATCH', 'DELETE'],'/api/tasks/{id}', [TestTasks::CLASS, 'tasks']);
Route::match(['GET', 'POST'],'/api/tasks', [TestTasks::CLASS, 'tasks']);
Route::post('/api/users', [TestTasks::CLASS, 'getUsers']);
Route::post('/api/login', [TestTasks::CLASS, 'login']);
Route::post('/api/logout', [TestTasks::CLASS, 'logout']);
Route::post('/api/registration', [TestTasks::CLASS, 'registration']);

