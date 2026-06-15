<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pantaubaca.index');
Route::view('/index.html', 'pantaubaca.index');
Route::view('/login.html', 'pantaubaca.login');
Route::view('/register.html', 'pantaubaca.register');
Route::view('/katalog.html', 'pantaubaca.katalog');
Route::view('/reader.html', 'pantaubaca.reader');
Route::view('/dashboard-siswa.html', 'pantaubaca.dashboard-siswa');
Route::view('/dashboard-guru.html', 'pantaubaca.dashboard-guru');
Route::view('/dashboard-admin.html', 'pantaubaca.dashboard-admin');
