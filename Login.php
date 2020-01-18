<?php

require_once('models/DatabaseModel.php');
header('Content-type: application/json');

$db = new DatabaseModel();

$returnArray = array(
    'code' => 403,
    'message' => "Access denied. There's no API key specified. Please specify an API key."
); // Make bad request error as default return message

// Check if user specified an API key
if (!isset($_POST['api_key'])) {
    echo json_encode($returnArray);
    exit;

} else {
    $apiKey = $_POST['api_key'];
    $db->connect(true);
    $db->select(
        "api",
        "api_key",
        null,
        "api_key = '" . $apiKey . "'"
    );

    $db->disconnect();

    // Check if specified API key doesn't exists
    if ($db->numRows() == 0) {
        $returnArray['code'] = 403;
        $returnArray['message'] = "Access denied. The specified API key doesn't exists. Please specify a valid API key.";
        echo json_encode($returnArray);
        exit;
    }

}

// Check if the required parameters aren't set
if (!isset($_POST['email']) || !isset($_POST['password'])) {
    $returnArray['code'] = 400;
    $returnArray['message'] = "Bad request. The given input didn't match with the needed input. Please try it again.";
    echo json_encode($returnArray);
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];
$db->connect(true);

// Execute a select query to see if the email and password exists in the database, if so return one row
$db->select(
    "user",
    "email, password",
    null,
    "email = '" . $email . "' AND password = '" . $password . "'"
);

$db->disconnect();

// Check if given user exists in the database
if ($db->numRows() == 1) {
    $returnArray['code'] = 200;
   
    echo json_encode($returnArray);
} else {
    $returnArray['code'] = 404;
    $returnArray['message'] = "The given data doesn't match with any user in the database.";
    echo json_encode($returnArray);
}

?>