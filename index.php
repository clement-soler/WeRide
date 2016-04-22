<?php

/* Require Slim and plugins */
require 'Slim/Slim.php';
require 'plugins/NotORM.php';

/* Register autoloader and instantiate Slim */
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

/* Database Configuration */
$dbhost   = 'hostname';
$dbuser   = 'username';
$dbpass   = 'password';
$dbname   = 'garage';
$dbmethod = 'mysql:dbname=';

$dsn = $dbmethod.$dbname;
$pdo = new PDO($dsn, $dbuser, $dbpass);
$db = new NotORM($pdo);

/* Routes */

// Home route
$app->get('/', function(){
    echo 'Welcome - WeRide API';
});

// Get all bikes
$app->get('/bikes', function() use($app, $db){
    $bikes = array();
    foreach ($db->vehicles() as $bike) {
        $bikes[]  = array(
            'id' => $bike['id'],
            'year' => $bike['year'],
            'make' => $bike['make'],
            'model' => $bike['model'],
            'linkimg' => $bike['img']
        );
    }
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($bikes, JSON_FORCE_OBJECT);
});

// Get a category bike
$app->get('/bikes/:type', function($type) use($app, $db){
    $bikes = array();
    $data = $db->vehicles()->where('type', $type);
    foreach ($data as $bike) {
        $bikes[]  = array(
            'id' => $bike['id'],
            'year' => $bike['year'],
            'make' => $bike['make'],
            'model' => $bike['model'],
            'linkimg' => $bike['img'],
            'about' => $bike['about'],
            'type' => $bike['type'],
            'date' => $bike['date']
        );
    }
    if(!empty($bikes)){
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($bikes, JSON_FORCE_OBJECT);
    }     
    else{
        echo json_encode(array(
            'status' => false,
            'message' => "$type is not a bike category"
        ));
    }   
});

// Get a single bike
$app->get('/bike/:id', function($id) use ($app, $db) {
    $app->response()->header("Content-Type", "application/json");
    $bike = $db->vehicles()->where('id', $id);
    if($data = $bike->fetch()){
        echo json_encode(array(
            'id' => $data['id'],
            'year' => $data['year'],
            'make' => $data['make'],
            'model' => $data['model'],
            'linkimg' => $data['img']
        ));
    }
    else{
        echo json_encode(array(
            'status' => false,
            'message' => "bike ID $id does not exist"
        ));
    }
});

// Add a new bike
$app->post('/bike', function() use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $bike = $app->request()->post();
    $result = $db->vehicles->insert($bike);
    echo json_encode(array('id' => $result['id']));
});

// Update a bike
$app->put('/bike/:id', function($id) use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $bike = $db->vehicles()->where("id", $id);
    if ($bike->fetch()) {
        $post = $app->request()->put();
        $result = $bike->update($post);
        echo json_encode(array(
            "status" => (bool)$result,
            "message" => "bike updated successfully"
            ));
    }
    else{
        echo json_encode(array(
            "status" => false,
            "message" => "bike id $id does not exist"
        ));
    }
});

// Remove a bike
$app->delete('/bike/:id', function($id) use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $bike = $db->vehicles()->where('id', $id);
    if($bike->fetch()){
        $result = $bike->delete();
        echo json_encode(array(
            "status" => true,
            "message" => "bike deleted successfully"
        ));
    }
    else{
        echo json_encode(array(
            "status" => false,
            "message" => "bike id $id does not exist"
        ));
    }
});

/* Run the application */
$app->run();