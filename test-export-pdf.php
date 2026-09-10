
<?php

use App\Http\Controllers\ExportPdfController;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$controller = new ExportPdfController;
$request = new Request;
$response = $controller->catalog($request);
file_put_contents(__DIR__.'/export-katalog.pdf', $response->getContent());
echo 'Katalog PDF size: '.filesize(__DIR__.'/export-katalog.pdf').' bytes
';
