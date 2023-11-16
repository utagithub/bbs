<?php
require __DIR__ . '/../vendor/autoload.php';

use GeoIp2\Database\Reader;

// This creates the Reader object, which should be reused across
// lookups.
$cityDbReader = new Reader('../GeoDB/GeoLite2-City.mmdb');

// Replace "city" with the appropriate method for your database, e.g.,
// "country".
//$record = $cityDbReader->city('128.101.101.101');
//
//echo $record->country->isoCode . "\n"; // 'US'
//echo $record->country->name . "\n"; // 'United States'
//echo $record->country->names['zh-CN'] . "\n"; // '美国'


srand(0);

//$reader = new Reader('GeoIP2-City.mmdb');
$reader = new Reader('../GeoDB/GeoLite2-City.mmdb');
$count = 50;
$startTime = microtime(true);
for ($i = 0; $i < $count; ++$i) {
    $ip = long2ip(rand(0, 2 ** 32 - 1));

    try {
        $t = $reader->city($ip);
    } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
    }
    if ($i % 10 === 0) {
        echo $i . ' ' . $ip . "\n";
    }
}
$endTime = microtime(true);

$duration = $endTime - $startTime;
echo 'Requests per second: ' . $count / $duration . "\n";