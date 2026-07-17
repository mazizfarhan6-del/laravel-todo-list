<?php

use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TodoController::class, 'index'])->name('todos.index');
Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
Route::put('/todos/{todo}/status', [TodoController::class, 'updateStatus'])->name('todos.updateStatus');
Route::delete('/todos/{todo}', [TodoController::class, 'destroy'])->name('todos.destroy');

// Rute Baru untuk fitur Restore & Hapus Permanen
Route::post('/todos/{id}/restore', [TodoController::class, 'restore'])->name('todos.restore');
Route::delete('/todos/{id}/force-delete', [TodoController::class, 'forceDelete'])->name('todos.forceDelete');