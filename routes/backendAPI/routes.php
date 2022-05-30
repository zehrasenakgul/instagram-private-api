<?php

use App\Http\Controllers\splenperAPIController;

Route::controller(splenperAPIController::class)->group(function () {
    Route::group(["prefix" => "splenperAPI"], function () {
        Route::get("/", "splenperAPI")->name(".splenperAPI");
    });
});
