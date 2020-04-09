<?php

require_once("C:/xampp/htdocs/stjepan_project/config/database_handler.php");

class products {
    private $database_handler;
    private $product_id;

    public function __construct( $database_handler ) {

        $this->database_handler = $database_handler;

    }

    public function setProductId($productid) {

        $this->productid = $productid;

    }

    // hämtar en product med product ID

    public function fetchSingleProduct() {

        $query_string = "SELECT id, producttitle, pric, size, productcreated FROM products WHERE id=:productid";
        $statementHandler = $this->database_handler->prepare($query_string);

        if($statementHandler !== false) {
            
            $statementHandler->bindParam(":productid", $this->productid);
            $statementHandler->execute();

            return $statementHandler->fetch();



        } else {
            echo "Could not create database statement!";
            die();
        }
    }

    // hämtar all producter

    public function fetchAllProduct() {

        $query_string = "SELECT id, producttitle, pric, size, productcreated FROM products";
        $statementHandler = $this->database_handler->prepare($query_string);

        if($statementHandler !== false) {

            $statementHandler->execute();
            return $statementHandler->fetchAll();

        } else {
            echo "Could not create database statement!";
            die();
        }
        
    }

    // lägger till product 

    public function addProduct($token_param, $productTitle_param, $pric_param, $size_param) {

        $query_string = "INSERT INTO products (producttitle, pric, size, token) VALUES(:producttitle, :pric, :size, :token)";
        $statementHandler = $this->database_handler->prepare($query_string);

        if($statementHandler !== false) {

            $statementHandler->bindParam(":token", $token_param);
            $statementHandler->bindParam(":producttitle", $productTitle_param);
            $statementHandler->bindParam(":pric", $pric_param);
            $statementHandler->bindParam(":size", $size_param);
            
            $success = $statementHandler->execute();

            if($success === true) {
                echo "OK!";
            } else {
                echo "Error while trying to insert post to database!";
            }

        } else {
            echo "Could not create database statement!";
            die();
        }
    }

    // ändrar product info på en befintlig product

    public function updateProduct($data) {


        if(!empty($data['productTitle'])) {
            $query_string = "UPDATE products SET productTitle=:productTitle WHERE id=:productid";
            $statementHandler = $this->database_handler->prepare($query_string);

            $statementHandler->bindParam(":productTitle", $data['productTitle']);
            $statementHandler->bindParam(":productid", $data['productid']);

            $statementHandler->execute();
            
        }

        if(!empty($data['pric'])) {
            $query_string = "UPDATE products SET pric=:pric WHERE id=:productid";
            $statementHandler = $this->database_handler->prepare($query_string);

            $statementHandler->bindParam(":pric", $data['pric']);
            $statementHandler->bindParam(":productid", $data['productid']);

            $statementHandler->execute();
            
        }
        if(!empty($data['size'])) {
            $query_string = "UPDATE products SET size=:size WHERE id=:productid";
            $statementHandler = $this->database_handler->prepare($query_string);

            $statementHandler->bindParam(":size", $data['size']);
            $statementHandler->bindParam(":productid", $data['productid']);

            $statementHandler->execute();
            
        }

        $query_string = "SELECT id, productTitle, pric, size, productcreated FROM products WHERE id=:productid";
        $statementHandler = $this->database_handler->prepare($query_string);

        $statementHandler->bindParam(":productid", $data['productid']);
        $statementHandler->execute();
        
        return json_encode($statementHandler->fetch());


    }

    // tarbort en befintlig product

    public function deletProduct($productid) {


        $query_string = "DELETE FROM products WHERE id=:productid";
        $statementHandler = $this->database_handler->prepare($query_string);

        $statementHandler->bindParam(':productid', $productid);
        $statementHandler->execute();



    }

    // lägger till en product till varukorg

    public function addToCart( $token, $productid) {

        $query_string = "INSERT INTO orders (token, productid) VALUES(:token, :productid)";
        $statementHandler = $this->database_handler->prepare($query_string);
        
        if($statementHandler !== false) {

            $statementHandler->bindParam(":token", $token);
            $statementHandler->bindParam(":productid", $productid);
            
            $success = $statementHandler->execute();
            
            if($success === true) {
                echo "OK!";
            } else {
                echo "Error while trying to insert order to database!";
            }

        } else {
            echo "Could not create database statement!";
            die();
        }

    }

    // tarbort en product från varukorgen 

    public function deletFromCart($token,$orderid) {

        $query_string = "DELETE FROM orders WHERE id=:orderid";
        $statementHandler = $this->database_handler->prepare($query_string);

        $statementHandler->bindParam(':orderid', $orderid);
        
        $success = $statementHandler->execute();

        if($success === true) {
            echo "OK!";
        } else {
            echo "Error while trying to insert order to database!";
        }


    }

    // beställer din order 

    public function checkout($token) {

        $query_string = "INSERT INTO checkout SELECT * FROM orders WHERE token=:token";
        $statementHandler = $this->database_handler->prepare($query_string);
        
        if($statementHandler !== false) {

            $statementHandler->bindParam(":token", $token);
            
            $success = $statementHandler->execute();
            
            if($success === true) {
                $this->deletorders($token);
                echo "OK!";
            } else {
                echo "Error while trying to insert order to database!";
            }

        } else {
            echo "Could not create database statement!";
            die();
        }

    }

    // tarbort från order tabelen efter att du har checkat ut  

    private function deletOrders($token){

        $query_string = "DELETE FROM orders WHERE token=:token";
        $statementHandler = $this->database_handler->prepare($query_string);

        $statementHandler->bindParam(':token', $token);
        
        $success = $statementHandler->execute();

        if($success === true) {
            
        } else {
            echo "Error while trying to insert order to database!";
        }
    }

}


?>