<?php

 require_once("../object/userdbhandler.php");
    

    $userHandler = new User($databaseHandler);

    echo $userHandler->addUser($_POST['firstname'], $_POST['lastname'], $_POST['username'], $_POST['password'], $_POST['email']);


?>