<?php
require 'vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$manager = new ImageManager(new Driver());
$image = $manager->create(400, 300)->fill('ff0000');
$image->save('public/test_red.jpg');
echo "Done\n";
