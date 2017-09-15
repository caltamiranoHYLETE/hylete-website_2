<?php

/**
 * These PestXML usage examples were written for the Rollcall REST service 
 * (see https://github.com/educoder/rollcall)
 **/

require_once '../PestXML.php';
$pest = new PestXML('http://localhost:3000');

// Retrieve and iterate over the list of all Users
$users = $pest->get('/users.xml');
$content = '';
        
foreach($users->user as $user) {
    $content .= $user->{'display-name'}." (".$user->username.")\n";
}

$content .= "<br />";

// Create a new User 
$data = array(
    'user' => array(
        'username' => "jcricket",
        'password' => "pinocchio",
        'display_name' => "Jiminy Cricket",
        'kind' => "Student"
    ) 
);

$user = $pest->post('/users.xml', $data);
$content .= "New User's ID: ".$user->id."\n";
$content .= "\n";


// Update the newly created User's attributes
$data = array(
    'user' => array(
        'kind' => "Instructor",
        'metadata' => array(
            'gender' => 'male',
            'age' => 30
        )
    ) 
);

$pest->put('/users/'.$user->id.'.xml', $data);

// Retrieve the User
$user = $pest->get('/users/'.$user->id.'.xml');
$content .= "User XML: \n";
$content .= $user->asXML();
$content .= "<br />";
$content .= "Name: ".$user->{'display-name'}."\n";
$content .= "Kind: ".$user->kind."\n";
$content .= "Age: ".$user->metadata->age."\n";
$content .= "<br />";

// Delete the User
$user = $pest->delete('/users/'.$user->id.'.xml');

// Try to create a User with invalid data (missing username)
$data = array(
    'user' => array(
        'password' => "pinocchio",
        'display_name' => "Jiminy Cricket",
        'kind' => "Student"
    ) 
);

try {
    $user = $pest->post('/users.xml', $data);
} catch (Pest_InvalidRecord $e) {
    $content .= $e->getMessage();
    $content .= "<br />";
}

$stdout = fopen('php://output', 'w');
fwrite($stdout, $content);
fclose($stdout);

