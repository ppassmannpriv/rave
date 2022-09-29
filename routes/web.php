<?php
// Admin routing
Route::redirect('/admin', '/login');
Auth::routes(['register' => false]);
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index')->name('home');
    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

    // Event
    Route::delete('events/destroy', 'EventController@massDestroy')->name('events.massDestroy');
    Route::post('events/media', 'EventController@storeMedia')->name('events.storeMedia');
    Route::post('events/ckmedia', 'EventController@storeCKEditorImages')->name('events.storeCKEditorImages');
    Route::resource('events', 'EventController');

    // Event Tickets
    Route::delete('event-tickets/destroy', 'EventTicketsController@massDestroy')->name('event-tickets.massDestroy');
    Route::resource('event-tickets', 'EventTicketsController');

    // Content Category
    Route::delete('content-categories/destroy', 'ContentCategoryController@massDestroy')->name('content-categories.massDestroy');
    Route::resource('content-categories', 'ContentCategoryController');

    // Content Tag
    Route::delete('content-tags/destroy', 'ContentTagController@massDestroy')->name('content-tags.massDestroy');
    Route::resource('content-tags', 'ContentTagController');

    // Content Page
    Route::delete('content-pages/destroy', 'ContentPageController@massDestroy')->name('content-pages.massDestroy');
    Route::post('content-pages/media', 'ContentPageController@storeMedia')->name('content-pages.storeMedia');
    Route::post('content-pages/ckmedia', 'ContentPageController@storeCKEditorImages')->name('content-pages.storeCKEditorImages');
    Route::resource('content-pages', 'ContentPageController');

    // Event Ticket Code
    Route::delete('event-ticket-codes/destroy', 'EventTicketCodeController@massDestroy')->name('event-ticket-codes.massDestroy');
    Route::resource('event-ticket-codes', 'EventTicketCodeController');

    // Order
    Route::delete('orders/destroy', 'OrderController@massDestroy')->name('orders.massDestroy');
    Route::resource('orders', 'OrderController');

    // Payment
    Route::delete('payments/destroy', 'PaymentController@massDestroy')->name('payments.massDestroy');
    Route::resource('payments', 'PaymentController');

    Route::get('paymentMethods/index', 'PaymentMethodController@index')->name('paymentMethods.index');
    Route::post('paymentMethods/upload', 'PaymentMethodController@uploadCsv')->name('paymentMethods.uploadCsv');
});
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth']], function () {
    // Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
    }
});

// Events stuff
Route::get('/events/', '\App\Http\Controllers\Site\EventController@index')->name('events.list');
Route::get('/events/{id}', '\App\Http\Controllers\Site\EventController@show')->name('events.show')->middleware('password');

// Cart logical routes
Route::get('/cart', '\App\Http\Controllers\Site\CartController@index')->name('cart.index');
Route::post('/cart/add', '\App\Http\Controllers\Site\CartController@addToCart')->name('cart.add');
Route::get('/cart/remove/{id}', '\App\Http\Controllers\Site\CartController@removeFromCart')->name('cart.remove');
Route::post('/cart/order', '\App\Http\Controllers\Site\CartController@orderCart')->name('cart.order');
Route::get('/cart/success', '\App\Http\Controllers\Site\CartController@success')->name('cart.success');

use App\Contracts\Navigation;

// CMS Style routing
Route::group([], function () {
    try {
        // Default index route stuff
        // Route::redirect('/', '/site');
        Route::get('/', 'Site\SiteController@index')->name('site.index');

        // Get pages into router
        $navigationService = App::make(Navigation::class);
        foreach ($navigationService->getPages() as $page) {
            Route::get($page->path, 'Site\SiteController@page')->name($page->path);
        }

        // Fallback index route aka noIndex
        if ($navigationService->getIndexPage() === null) {
            Route::get('/', 'Site\SiteController@noIndex')->name('site.index');
        }
    } catch (\Throwable $throwable) {
        report($throwable);
    }
});

Route::get('/site-error', 'Site\SiteController@error')->name('site.error');

// Handle 404 and other errors
Route::fallback(function() {
    return redirect('/site-error')->with('warning', 'Sorry, this route is not available.');
});
