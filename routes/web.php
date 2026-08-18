<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Pages\Login;


// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('insert', function () {
//     $tes = app('firebase.firestore')->database()->collection('tes')->newDocument();

//     $tes->set([
//         'name' => 'oke'
//     ]);
// });



Route::get('/', Login::class)->name('login');
