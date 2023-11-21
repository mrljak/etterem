<?php

require './router.php';
require './slugifier.php';
require './dishes.php';
require './dishTypes.php';
require './auth.php';

$method = $_SERVER["REQUEST_METHOD"];
$parsed = parse_url($_SERVER['REQUEST_URI']);
$path = $parsed['path'];

// Útvonalak regisztrálása
$routes = [
    // [method, útvonal, handlerFunction],
    ['GET', '/', 'homeHandler'],
    ['GET', '/admin/etel-szerkesztese/{keresoBaratNev}', 'dishEditHandler'],
    ['GET', '/admin', 'adminHandler'],
    ['POST', '/login', 'loginHandler'],
    ['GET', '/admin/uj-etel-letrehozasa', 'dishCreateFormHandler'],
    ['POST', '/create-dish', 'dishCreateHandler'],
    ['POST', '/update-dish/{dishId}', 'dishUpdateHandler'],
    ['POST', '/delete-dish/{dishId}', 'dishDeleteHandler'],
    ['GET', '/admin/etel-tipusok', 'dishTypesFormHandler'],
    ['POST', '/create-dish-type', 'dishTypeCreateHandler'],
    ['POST', '/logout', 'logoutHandler'],
    
];

// Útvonalválasztó inicializálása
$dispatch = registerRoutes($routes);
$matchedRoute = $dispatch($method, $path);
$handlerFunction = $matchedRoute['handler'];
$handlerFunction($matchedRoute['vars']);

// Handler függvények deklarálása
function homeHandler()
{
    
    $dishList = getDishes('active');
    $dishTypes = getDishTypes();
    
    //echo '<pre>';
    //var_dump($dishTypes);
    echo render("wrapper.phtml",[
        "content"=> render("public-menu.phtml",[
            "dishTypes"=> $dishTypes,
            "dishes"=> $dishList
        ])
    ]);
}

function adminHandler() {
    if(!isLoggedIn()) {
        echo render("wrapper.phtml", [
            'content' => render('login.phtml', [
                           
            ]),
            'isAuthorized' => false  //mivel ide csak akkor jutunk, ha nincs bejelentkezve
        ]);
        return;
    } 

    $dishes = getDishes('all');
    $dishTypes = getDishTypes();

    echo render("admin-wrapper.phtml",[
        "content"=> render("dish-list.phtml",[
            "dishTypes"=> $dishTypes,
            "dishes"=> $dishes
        ])
    ]);
}


function notFoundHandler()
{
    echo 'Oldal nem található';
}

function render($path, $params = [])
{
    ob_start();
    require __DIR__ . '/views/' . $path;
    return ob_get_clean();
}

