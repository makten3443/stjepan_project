<?php
    require_once("C:/xampp/htdocs/stjepan_project/config/db_handler.php");
    

    class User {

        private $databaseHandler;

        public function __construct($databaseHandlerParam)
        {
            $this->databaseHandler = $databaseHandlerParam;
        }
        public function addUser($firstname_IN, $lastname_IN, $username_IN, $password_IN, $email_IN) {
            $return_object = new stdClass();

            if($this->isUsernameTaken($username_IN) === false) {
                if($this->isEmailTaken($email_IN) === false) {

                    
                    $return = $this->insertUserToDatabase($firstname_IN, $lastname_IN, $username_IN, $password_IN, $email_IN);
                    if($return !== false) {

                        $return_object->state = "SUCCESS";
                        $return_object->user = $return;

                    }  else {

                        $return_object->state = "ERROR";
                        $return_object->message = "Something went wrong when trying to INSERT user";

                    }


                } else {
                    $return_object->state = "ERROR";
                    $return_object->message = "Email is taken";
                }

            } else {
                
                $return_object->state = "ERROR";
                $return_object->message = "Username is taken";
            }
                

            return json_encode($return_object);
        }
        private function insertUserToDatabase($firstname_param,$lastname_param,$username_param, $password_param, $email_param) {

                $query_string = "INSERT INTO users (firstname, lastname, username, password, email) VALUES(:firstname, :lastname, :username, :password, :email)";
                $statementHandler = $this->databaseHandler->prepare($query_string);

                if($statementHandler !== false ){

                    $encrypted_password = md5($password_param);

                    $statementHandler->bindParam(':firstname', $firstname_param);
                    $statementHandler->bindParam(':lastname', $lastname_param);
                    $statementHandler->bindParam(':username', $username_param);
                    $statementHandler->bindParam(':password', $encrypted_password);
                    $statementHandler->bindParam(':email', $email_param);

                    $statementHandler->execute();


                    $last_inserted_user_id = $this->databaseHandler->lastInsertId();

                    $query_string = "SELECT id, username, email FROM users WHERE id=:last_user_id";
                    $statementHandler = $this->databaseHandler->prepare($query_string);

                    $statementHandler->bindParam(':last_user_id', $last_inserted_user_id);

                    $statementHandler->execute();

                    return $statementHandler->fetch();
                    

                } else {
                    return false;
                }


            }
            private function isUsernameTaken( $username_param ) {

                $query_string = "SELECT COUNT(id) FROM users WHERE username=:username";
                $statementHandler = $this->databaseHandler->prepare($query_string);

                if($statementHandler !== false ){

                    $statementHandler->bindParam(":username", $username_param);
                    $statementHandler->execute();

                    $numberOfUsernames = $statementHandler->fetch()[0];

                    if($numberOfUsernames > 0) {
                        return true; 
                    } else {
                        return false;
                    }


                } else {
                    echo "Statementhandler epic fail!";
                    die;
                }
            }

            private function isEmailTaken( $email_param ) {
                $query_string = "SELECT COUNT(id) FROM users WHERE email=:email";
                $statementHandler = $this->databaseHandler->prepare($query_string);

                if($statementHandler !== false ){

                    $statementHandler->bindParam(":email", $email_param);
                    $statementHandler->execute();

                    $numberOfUsers = $statementHandler->fetch()[0];

                    if($numberOfUsers > 0) {
                        return true; 
                    } else {
                        return false;
                    }


                } else {
                    echo "Statementhandler epic fail!";
                    die;
                }
            }

        }
?>