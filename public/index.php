<?php

session_start();

$config = require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Router.php';
require __DIR__ . '/../app/Models/Club.php';
require __DIR__ . '/../app/Models/Event.php';
require __DIR__ . '/../app/Controllers/AuthController.php';
require __DIR__ . '/../app/Controllers/EventController.php';

$db = (new Database($config))->getConnection();
$router = new Router();

$clubModel = new Club($db);
$eventModel = new Event($db);
$authController = new AuthController($clubModel);
$eventController = new EventController($eventModel, $clubModel);

$router->get('/', fn() => $eventController->publicFeed());
$router->get('/api/events', fn() => $eventController->apiEvents());
$router->get('/login', fn() => $authController->showLogin());
$router->post('/login', fn() => $authController->login());
$router->post('/logout', fn() => $authController->logout());
$router->get('/dashboard', fn() => $eventController->dashboard());
$router->post('/events/create', fn() => $eventController->store());
$router->post('/events/update', fn() => $eventController->update());
$router->post('/events/delete', fn() => $eventController->delete());

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);