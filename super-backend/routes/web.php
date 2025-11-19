<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

// Página de teste interativa em Vue.js
Route::get('/test-api', function () {
    return view('docs-vue');
});

// Documentação formal em Vue.js
Route::get('/documentation', function () {
    return view('documentation');
});

// Documentação estática do Scribe (mantida para referência futura)
// Acesse em /docs-api
