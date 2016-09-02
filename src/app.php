<?php

use Slim\Slim;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use tdt4237\webapp\Auth;
use tdt4237\webapp\Hash;
use tdt4237\webapp\repository\UserRepository;
use tdt4237\webapp\repository\PatentRepository;

require_once __DIR__ . '/../vendor/autoload.php';

chdir(__DIR__ . '/../');
chmod(__DIR__ . '/../web/uploads', 0777);

$app = new Slim([
    'templates.path' => __DIR__.'/webapp/templates/',
    'debug' => true,
    'view' => new Twig()

]);

$view = $app->view();
$view->parserExtensions = array(
    new TwigExtension(),
);

try {
    // Create (connect to) SQLite database in file
    $app->db = new PDO('sqlite:app.db');
    // Set errormode to exceptions
    $app->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}

// Wire together dependencies

date_default_timezone_set("Europe/Oslo");

$app->hash = new Hash();
$app->userRepository = new UserRepository($app->db);
$app->patentRepository = new PatentRepository($app->db);
$app->auth = new Auth($app->userRepository, $app->hash);

$ns ='tdt4237\\webapp\\controllers\\';

// Static pages
$app->get('/', $ns . 'PagesController:frontpage');
$app->get('/aboutus', $ns . 'PagesController:aboutUs');

// Login form
$app->get('/login', $ns . 'SessionsController:new');
$app->post('/login', $ns . 'SessionsController:create');

$app->get('/logout', $ns . 'SessionsController:destroy')->name('logout');

// New user
$app->get('/user/new', $ns . 'UserController:index')->name('newuser');
$app->post('/user/new', $ns . 'UserController:create');

// Edit logged in user
$app->get('/user/edit', $ns . 'UserController:showUserEditForm')->name('editprofile');
$app->post('/user/edit', $ns . 'UserController:receiveUserEditForm');

// Show a user by name
$app->get('/user/:username', $ns . 'UserController:show')->name('showuser');

// Patents
$app->get('/patents', $ns . 'PatentsController:index')->name('showpatents');

$app->get('/patents/new', $ns . 'PatentsController:new')->name('registerpatent');
$app->post('/patents/new', $ns . 'PatentsController:create');

$app->get('/patents/:patentId', $ns . 'PatentsController:show');

// Admin restricted area
$app->get('/admin', $ns . 'AdminController:index')->name('admin');
$app->get('/admin/delete/patent/:patentid', $ns . 'AdminController:deletepatent');
$app->get('/admin/delete/:username', $ns . 'AdminController:delete');


return $app;
