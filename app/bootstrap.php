<?php

$loader = require __DIR__.'/../vendor/autoload.php';

use KnpU\CodeBattle\Application;
use Doctrine\Common\Annotations\AnnotationRegistry;

// configure the annotation class loader
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

/*
 * Create our application object
 *
 * This configures all of the routes, providers, etc (in the constructor)
 */

$app = new Application(array(
    'debug' => true,
));
/** show all errors! */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/*
 ************* OTHER SETUP ******************
 */

if (!file_exists($app['sqlite_path'])) {
    /** @var \KnpU\CodeBattle\DataFixtures\FixturesManager $fixtures */
    $fixtures = $app['fixtures_manager'];
    $fixtures->resetDatabase();
    $fixtures->populateData($app);
}


/*
 ************* CONTROLLERS ******************
 */

// dynamically/magically loads all of the controllers in the Controller directory
$app->mountControllers();

return $app;
