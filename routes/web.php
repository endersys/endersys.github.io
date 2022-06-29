<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;

Route::get('/', [EventController::class, 'index']);
Route::prefix('eventos')->name('events.')->group(function(){
    Route::get('', [EventController::class, 'index'])->name('index');
    Route::get('exibir/{id}', [EventController::class, 'show'])->name('show');
    Route::get('cadastrar', [EventController::class, 'create'])->name('create')->middleware('auth');
    Route::post('salvar', [EventController::class, 'store'])->name('store');
    Route::get('editar/{id}', [EventController::class, 'edit'])->name('edit')->middleware('auth');
    Route::put('atualizar/{id}', [EventController::class, 'update'])->name('update')->middleware('auth');
    Route::delete('deletar/{id}', [EventController::class, 'destroy'])->name('destroy')->middleware('auth');
    Route::get('dashboard', [EventController::class, 'dashboard'])->name('dashboard')->middleware('auth');
    Route::post('entrar/{id}', [EventController::class, 'joinEvent'])->name('joinEvent')->middleware('auth');
    Route::delete('sair/{id}', [EventController::class, 'leaveEvent'])->name('leaveEvent')->middleware('auth');
});
