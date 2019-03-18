<?php
Route::group(['module' => 'Frontend'], function () {

    Route::get('/welcome', function () {
        echo "Welcome Quiz";
    });

});
?>