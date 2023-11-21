<?php
function dishTypeCreateHandler(){
    //echo '<pre>';
    //var_dump($_POST);
    //exit;
    redirectToLoginIfNotLoggedIn();
    $name = $_POST['name'];
    $slug = slugify($name);
    $description = $_POST['description'];
    
    
    $pdo = getConnection();
    $statement = $pdo->prepare("INSERT INTO dishTypes (name, slug, description) 
        VALUES (:name, :slug, :description)");

    $statement->execute([
        'name' => $name,
        'slug' => $slug,
        'description' => $description
    ]);
    header('Location: /admin/etel-tipusok?siker=1');
}


function dishTypesFormHandler() {
    redirectToLoginIfNotLoggedIn();
    $dishTypes = getDishTypes();
    echo render("admin-wrapper.phtml", [
        'content' => render('dish-type-list.phtml', [ 
            'dishTypes' => $dishTypes,
        ])
    ]);
}


function getDishTypes() {
    $pdo = getConnection();
    $statement = $pdo->prepare('SELECT * from dishTypes ORDER BY id ASC');
    $statement->execute();
    $dishTypes = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    return $dishTypes;
}
?>