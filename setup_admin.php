<?php
require 'bootstrap/app.php';

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Get application from bootstrap
$app = require 'bootstrap/app.php';
$app->make('config');

// Update admin password
DB::table('users')->where('role', 'admin')->update([
    'password' => bcrypt('admin123')
]);

echo "Admin password set to: admin123\n";
