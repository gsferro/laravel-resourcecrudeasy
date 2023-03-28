<?php

Route::group(['namespace' => 'Gsferro\ResourceCrudEasy\Controllers', 'middleware' => ['web']], function()
{
    Route::any('/datatables', 'DatatablesController')->name('datatables');
});
