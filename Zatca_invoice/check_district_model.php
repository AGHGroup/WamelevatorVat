<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$d = App\Models\District::with('city.region')->find('11302270066');
if (!$d) { echo "District not found\n"; exit; }

echo "district name_ar: " . $d->name_ar . "\n";
echo "city city_name:   " . ($d->city?->city_name ?? 'NULL') . "\n";
echo "region name_ar:   " . ($d->region?->name_ar ?? 'NULL') . "\n";
