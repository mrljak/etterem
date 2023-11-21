<?php
function dishCreateFormHandler() {
    redirectToLoginIfNotLoggedIn();
    $dishTypes = getDishTypes();
    echo render("admin-wrapper.phtml", [
        'content' => render('create-dish.phtml', [ 
            'dishTypes' => $dishTypes,
        ])
    ]);
}


function dishCreateHandler(){
    //echo '<pre>';
    //var_dump($_POST);
    //exit;
    redirectToLoginIfNotLoggedIn();
    $name = $_POST['name'];
    $slug = slugify($name);
    $description = $_POST['description'];
    $price = $_POST['price'];
    $dishTypeId = $_POST['dishTypeId'];
    $isActive = $_POST['isActive'] = 'on' ? 1 : 0;
    
    $pdo = getConnection();
    $statement = $pdo->prepare("INSERT INTO dishes (name, slug, description, price, isActive, dishTypeId) 
        VALUES (:name, :slug, :description, :price, :isActive, :dishTypeId)");

    $statement->execute([
        'name' => $name,
        'slug' => $slug,
        'description' => $description, 
        'price' => $price, 
        'isActive' => $isActive,
        'dishTypeId' => $dishTypeId
    ]);
    header('Location: /admin?siker=1');
}

function dishEditHandler($vars)
{
    redirectToLoginIfNotLoggedIn();
    $pdo = getConnection();
    $statement = $pdo->prepare("SELECT * from dishes WHERE slug=?");
    $statement->execute([$vars['keresoBaratNev']]);

    $dish = $statement->fetch(PDO::FETCH_ASSOC);
    $dishTypes = getDishTypes();

    echo render("wrapper.phtml", [
        "content" => render("edit-dish.phtml", [
            "dish"=>$dish,
            "dishTypes"=>$dishTypes,
        ])
    ]);
    return;
    
    
   //echo "<pre>";
    //var_dump($dish);
    //echo 'Étel szerkesztése: ' . $vars['keresoBaratNev'];
}


function dishUpdateHandler($urlParams) {
    //echo "<pre>";
    //var_dump($urlParams);
    //var_dump($_POST);
    //exit;
    redirectToLoginIfNotLoggedIn();
    $url = $_SERVER['REQUEST_URI'];
    //$dishId = explode("/",$url)[2];
    
    $newDish = [
        ':name' => $_POST["name"],
        ':slug' => $_POST["slug"],
        ':description' => $_POST["description"],
        ':price' => $_POST["price"],
        ':isActive' => isset($_POST["isActive"]) ? 1 : 0,
        ':dishTypeId' => $_POST["dishTypeId"],
        ':id' => $urlParams['dishId']
        ];

    $sql = "UPDATE dishes SET 
            name = :name, 
            slug = :slug, 
            description = :description, 
            price = :price, 
            isActive = :isActive, 
            dishTypeId = :dishTypeId 
        WHERE dishes.id = :id";

    $pdo = getConnection();
    $statement = $pdo->prepare($sql);
    $statement->execute($newDish);
    header('Location: /admin?update=1');
}

function dishDeleteHandler($urlParams) { 
    redirectToLoginIfNotLoggedIn();
    $url = $_SERVER['REQUEST_URI'];
    $dishId = $urlParams['dishId'];
    
    
    $pdo = getConnection();
    $statement = $pdo->prepare("DELETE FROM dishes WHERE id = ?");
    $result = $statement->execute([$dishId]);
    $result ? header('Location: /admin?delete=1') : header('Location: /admin?delete=0');
}


function getDishes($param) {
    $statementText = '';
    switch($param) {
        case 'all': 
            $statementText = "SELECT * from dishes ORDER BY name ASC";
            break;
        case 'active':
            $statementText = "SELECT * from dishes WHERE isActive=1 ORDER BY name ASC";
            break;
        case 'inactive':
            $statementText = "SELECT * from dishes WHERE isActive=0 ORDER BY name ASC";
            break;
        default:
            return '';
    }
    $pdo = getConnection();
    $statement = $pdo->prepare($statementText);
    $statement->execute();
    $dishes = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $dishes;
}

?>
