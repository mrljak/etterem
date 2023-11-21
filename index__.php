<?php

require './router.php';
require './slugifier.php';

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
];

// Útvonalválasztó inicializálása
$dispatch = registerRoutes($routes);
$matchedRoute = $dispatch($method, $path);
$handlerFunction = $matchedRoute['handler'];
$handlerFunction($matchedRoute['vars']);

// Handler függvények deklarálása
function homeHandler()
{
    $pdo = getConnection();
    $statement = $pdo->prepare('SELECT * from dishTypes');
    $statement->execute();
    $dishTypes = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    $statement = $pdo->prepare('SELECT * from dishes WHERE isActive=1');
    $statement->execute();
    $dishes = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    
    //echo '<pre>';
    //var_dump($dishTypes);
    echo render("wrapper.phtml",[
        "content"=> render("public-menu.phtml",[
            "dishTypes"=> $dishTypes,
            "dishes"=> $dishes
        ])
    ]);
}

function loginHandler() {
    $pdo = getConnection();
    $statement = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $statement->execute([$_POST["email"]]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    //echo "<pre>";
    //var_dump($user);
    if(!$user) {
        header('Location: ' . getPathWithId($_SERVER['HTTP_REFERER']) . '&info=invalidCredentials');
        return;
    }
   
    $isVerified = password_verify($_POST['password'], $user["password"]);
    if(!$isVerified) {
        header('Location: ' . getPathWithId($_SERVER['HTTP_REFERER']) . '&info=invalidCredentials');
        return;
    };

    session_start();
    $_SESSION["userId"] = $user["id"];
    header('Location: ' . getPathWithId($_SERVER['HTTP_REFERER']));
        

}

function adminHandler() {
    if(!isLoggedIn()) {
        echo render("wrapper.phtml", [
            'content' => render('login.phtml', [
                'url'=> getPathWithId($_SERVER['REQUEST_URI']),           
            ]),
            'isAuthorized' => false  //mivel ide csak akkor jutunk, ha nincs bejelentkezve
        ]);
        return;
    }   
    
    
    echo render("wrapper.phtml",[
        "content"=> render("admin-wrapper.phtml",[])
    ]);


}


function dishEditHandler($vars)
{
    echo "<pre>";
    var_dump($vars);
    echo 'Étel szerkesztése: ' . $vars['keresoBaratNev'];
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

function getConnection()
{
    return new PDO(
        'mysql:host=' . $_SERVER['DB_HOST'] . ';dbname=' . $_SERVER['DB_NAME'],
        $_SERVER['DB_USER'],
        $_SERVER['DB_PASSWORD']
    );
}

function isLoggedIn(): bool {
    if(!isset($_COOKIE[session_name()])) {
        return false;
    };

    session_start();

    if(!isset($_SESSION['userId'])) {
        return false;
    };

    return true;
}

function getPathWithId($url) {
    $parsed = parse_url($url);
 
    if(!isset($parsed["query"])) {
        return $url;
    }
    
    $queryParams = [];
    parse_str($parsed["query"], $queryParams);

    return $parsed['path'] . "?id=" . $queryParams['id'];
}