<?php
function loginHandler() {
   
   $pdo = getConnection();
   $statement = $pdo->prepare("SELECT * FROM users WHERE email = ?");
   $statement->execute([$_POST["email"]]);
   $user = $statement->fetch(PDO::FETCH_ASSOC);

   if(!$user) {
       header('Location: /admin?info=invalidCredentials');
       return;
   }
  
   $isVerified = password_verify($_POST['password'], $user["password"]);

   if(!$isVerified) {
    header('Location: /admin?info=invalidCredentials');
       return;
   };

   session_start();
   $_SESSION["userId"] = $user["id"];
   header('Location: /admin');
}

function logoutHandler() {
    session_start();
    $params = session_get_cookie_params();
    setcookie(session_name(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
    header('Location: /');
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



function redirectToLoginIfNotLoggedIn() {
    if(isLoggedIn()) {
        return;
    }

    header('Location: /admin');
    exit;
}
?>