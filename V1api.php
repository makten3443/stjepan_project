<?php 
    
    require_once("C:/xampp/htdocs/stjepan_project/object/users.php");
    require_once("C:/xampp/htdocs/stjepan_project/object/products.php");

    // hämmtar allt från sign up formen och filtrerar så att vi inte får in skadlig data sen så lägger then till user i databasen

    if($_POST['post_type'] === "signup"){

        $firstname = ( isset($_POST['firstname']) ? $_POST['firstname'] : '' );
        $lastname = ( isset($_POST['lastname']) ? $_POST['lastname'] : '' );
        $email = ( isset($_POST['email']) ? $_POST['email'] : '' );
        $username = ( isset($_POST['username']) ? $_POST['username'] : '' );
        $password = ( isset($_POST['password']) ? $_POST['password'] : '' );

        $firstnameFiltered = filter_var($firstname, FILTER_SANITIZE_STRING);
        $lastnameFiltered = filter_var($lastname, FILTER_SANITIZE_STRING);
        $emailFiltered = filter_var($email, FILTER_SANITIZE_STRING);
        $usernameFiltered = filter_var($username, FILTER_SANITIZE_STRING);
        $passwordFiltered = filter_var($password, FILTER_SANITIZE_STRING);

        $userHandler = new User($databaseHandler);

        if(!empty($firstnameFiltered)){

            if(!empty($lastnameFiltered)){

                if(!empty($emailFiltered)){
                        
                    if(!empty($usernameFiltered)){

                        if(!empty($passwordFiltered)){

                            echo $userHandler->addUser($firstnameFiltered,$lastnameFiltered,$usernameFiltered,$passwordFiltered,$emailFiltered);

                        }else {
                            echo "password is empty";
                        }
                    }else {
                        echo "username is empty";
                    }
                }else {
                    echo "email is empty";
                }
            }else{
                echo "lastname is empty";
            }
        }else{
            echo "firstname is empty";
        }

    }
    
    // loggar in med user namn och password även filtrerar det vi hämtar kollar om man är admin

    elseif($_POST['post_type'] === "login"){

            $username = ( isset($_POST['username']) ? $_POST['username'] : '' );
            $password = ( isset($_POST['password']) ? $_POST['password'] : '' );

            $usernameFiltered = filter_var($username, FILTER_SANITIZE_STRING);
            $passwordFiltered = filter_var($password, FILTER_SANITIZE_STRING);

            $userHandler = new User($databaseHandler);

            if(!empty($usernameFiltered)){

                if(!empty($passwordFiltered)){

                   echo $userToken = $userHandler->loginUser( $usernameFiltered, $passwordFiltered);

                }else {
                    echo "password is empty";
                }

            }else {
                echo "username is empty";
            }
 
    }
    
    // lägger till product i databasen och filtrerar det vi lägger till i databasen och kollar om man är admin

    elseif($_POST['post_type'] === "addProduct"){

            $productObject = new products($databaseHandler);
            $userHandler = new User($databaseHandler);

            $token_IN = ( isset($_POST['token']) ? $_POST['token'] : '' );
            $productTitle_IN = ( isset($_POST['productTitle']) ? $_POST['productTitle'] : '' );
            $pric_IN = ( isset($_POST['pric']) ? $_POST['pric'] : '' );
            $size_IN = ( isset($_POST['size']) ? $_POST['size'] : '' );

            $tokenFiltered = filter_var($token_IN, FILTER_SANITIZE_STRING);
            $productTitleFiltered = filter_var($productTitle_IN, FILTER_SANITIZE_STRING);
            $pricFiltered = filter_var($pric_IN, FILTER_SANITIZE_STRING);
            $sizeFiltered = filter_var($size_IN, FILTER_SANITIZE_STRING);
            $token = $_POST['token'];

            if($userHandler->validateToken($token) === false) {
                echo "Invalid token!";
                die;
            }

            $isAdmin = $userHandler->isAdmin($token);

            if($isAdmin === false) {
                echo "You are not admin";
                die;
            }

            if(!empty($productTitleFiltered)){

                if(!empty($pricFiltered)){

                    if(!empty($sizeFiltered)){

                        $productObject->addProduct($tokenFiltered,$productTitleFiltered, $pricFiltered,$sizeFiltered);
                    }else {
                        echo "size is empty";
                    }
                } else {
                    echo "Error: price cannot be empty!";
                }
            } else {
                echo "Error: product titel cannot be empty!";
            }


            
    }
    
    // ändrar info för befintlig product och kollar om man är admin
    
    elseif($_POST['post_type'] === "updateProduct"){

            $productObject = new products($databaseHandler);
            $userHandler = new User($databaseHandler);

            if(!empty($_POST['token'])) {

                if(!empty($_POST['productid'])) { 
                    
                    $token = $_POST['token'];

                    if($userHandler->validateToken($token) === false) {
                        $retObject = new stdClass;
                        $retObject->error = "Token is invalid";
                        $retObject->errorCode = 1338;
                        echo json_encode($retObject);
                        die();
                    }

                    $isAdmin = $userHandler->isAdmin($token);

                    if($isAdmin === false) {
                        echo "You are not admin";
                        die;
                    }
                    
                    echo $productObject->updateProduct($_POST);

                } else {
                    $retObject = new stdClass;
                    $retObject->error = "Invalid id!";
                    $retObject->errorCode = 1336;

                    echo json_encode($retObject);
                }

            } else {
                $retObject = new stdClass;
                $retObject->error = "No token found!";
                $retObject->errorCode = 1337;

                echo json_encode($retObject);
            }
            

            
    }
    
    // tarbort befintlig product från databasen och kollar om man är admin
    
    elseif($_POST['post_type'] === "deletProduct"){

            $productObject = new products($databaseHandler);
            $userHandler = new User($databaseHandler);

            $token = ( isset($_POST['token']) ? $_POST['token'] : '' );
            $productid = ( isset($_POST['productid']) ? $_POST['productid'] : '' );

            $tokenFiltered = filter_var($token, FILTER_SANITIZE_STRING);
            $productidFiltered = filter_var($productid, FILTER_SANITIZE_STRING);

            $token = $_POST['token'];

            if($userHandler->validateToken($token) === false) {
                echo "Invalid token!";
                die;
            }

            $isAdmin = $userHandler->isAdmin($token);

            if($isAdmin === false) {
                echo "You are not admin";
                die;
            }

            if(!empty($tokenFiltered)){

                if(!empty($productidFiltered)){

                    $productObject->deletProduct($productidFiltered);
                }else {
                    echo "Product id is empty";
                }
            } else {
                echo "Error: token cannot be empty!";
            }
                
    }
    
    // hämtar en product och vissar den 
    
    elseif($_POST['post_type'] === "showOneProduct"){

            $productObject = new Products($databaseHandler);
            $userHandler = new User($databaseHandler);

            $productid = ( !empty($_POST['productid'] ) ? $_POST['productid'] : -1 );

            $token = $_POST['token'];

            if($userHandler->validateToken($token) === false) {
                echo "Invalid token!";
                die;
            }

            if($productid > -1) {
            
                $productObject->setProductId($productid);
                
                print_r( $productObject->fetchSingleProduct() );
            
            
            } else {
            
                echo "Error: Missing parameter id!";
            
            }
        
    }
    
    // hämtar all producter och vissar de
    
    elseif($_POST['post_type'] === "showAllProducts"){
        
            $productObject = new Products($databaseHandler);
            $userHandler = new User($databaseHandler);

            $token = $_POST['token'];

            if($userHandler->validateToken($token) === false) {
                echo "Invalid token!";
                die;
            }

            echo "<pre>";
            print_r($productObject->fetchAllProduct());
            echo "</pre>";

            

            
    }
    
    // lägger till in i varukorden

    elseif($_POST['post_type'] === "addToCart"){

            $productObject = new products($databaseHandler);
            $userHandler = new User($databaseHandler);

            $token = ( isset($_POST['token']) ? $_POST['token'] : '' );
            $productid = ( isset($_POST['productid']) ? $_POST['productid'] : '' );

            if($userHandler->validateToken($token) === false) {
                echo "Invalid token!";
                die;
            }

            if(!empty($token)){

                if(!empty($productid)){
                    
                    $productObject->addToCart($token, $productid);
                    
                }else {
                    echo "product id is empty";
                }
            } else {
                echo "Error: token cannot be empty!";
            }

    }
    
    // tarbort från varukorgen

    elseif($_POST['post_type'] === "deletFromCart"){

            $productObject = new products($databaseHandler);
            $userHandler = new User($databaseHandler);

            $token = ( isset($_POST['token']) ? $_POST['token'] : '' );
            $orderid = ( isset($_POST['orderid']) ? $_POST['orderid'] : '' );


            if($userHandler->validateToken($token) === false) {
                echo "Invalid token!";
                die;
            }

            if(!empty($token)){

                if(!empty($orderid)){

                    $productObject->deletFromCart($token, $orderid);
                }else {
                    echo "order id is empty";
                }
            } else {
                echo "Error: token cannot be empty!";
            }



    }
    
    // beställer din order 
    
    elseif($_POST['post_type'] === "checkout"){

            $productObject = new products($databaseHandler);
            $userHandler = new User($databaseHandler);

            $token = ( isset($_POST['token']) ? $_POST['token'] : '' );

            if($userHandler->validateToken($token) === false) {
                echo "Invalid token!";
                die;
            }

            if(!empty($token)){

            $productObject->checkout($token);

            }else {
                echo "Error: token cannot be empty!";
            }
    }
?>