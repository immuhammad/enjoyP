<?php
/**
 * Public alias for the application entry point
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
#phpinfo();
use Magento\Framework\App\Bootstrap;
// $auth_user = 'pls';
// $auth_pass = 'pls123_';
// $auth_web = 'http://20.91.187.25/';

// if($_SERVER['REQUEST_URI'] != "/"){
//     if (!isset($_SERVER['PHP_AUTH_USER']) or $_SERVER['PHP_AUTH_USER'] != $auth_user or $_SERVER['PHP_AUTH_PW'] != $auth_pass) {
//         header('WWW-Authenticate: Basic realm="Protected Area"');
//         header('HTTP/1.0 401 Unauthorized');
//         echo 'Please login  to access the website... <a href="'.$auth_web.'">'.$auth_web.'</a>';
    
//         exit;
        
//     }
// }

 
try {
    require __DIR__ . '/../app/bootstrap.php';
} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    http_response_code(500);
    exit(1);
}

$bootstrap = Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
$bootstrap->run($app);
