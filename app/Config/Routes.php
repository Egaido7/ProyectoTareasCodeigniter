<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes ->setDefaultNamespace('App\Controllers');
$routes ->setDefaultController('Home');
$routes ->setDefaultMethod('index');
$routes ->setTranslateURIDashes(false);
$routes -> set404Override();


$routes -> setAutoRoute(true);

//$routes->get('/', 'Home::index');

