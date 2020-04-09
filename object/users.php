<?php

require_once("C:/xampp/htdocs/stjepan_project/config/database_handler.php");

class User
{

    private $database_handler;
    private $username;
    private $token_validity_time = 15; // minutes



    public function __construct($database_handler_parameter_IN)
    {
        $this->database_handler = $database_handler_parameter_IN;
    }

    // skapar en användare

    public function addUser($firstname_IN,$lastname_IN,$username_IN, $password_IN, $email_IN)
    {
        $return_object = new stdClass();

        if ($this->isUsernameTaken($username_IN) === false) {
            if ($this->isEmailTaken($email_IN) === false) {


                $return = $this->insertUserToDatabase($firstname_IN,$lastname_IN,$email_IN,$username_IN, $password_IN);
                if ($return !== false) {

                    $return_object->state = "SUCCESS";
                    $return_object->user = $return;
                } else {

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

    //lägger till user i databasen

    private function insertUserToDatabase($firstname_param,$lastname_param,$email_param,$username_param, $password_param)
    {

        $query_string = "INSERT INTO users (firstname, lastname, email, username, password) VALUES(:firstname, :lastname, :email, :username, :password)";
        $statementHandler = $this->database_handler->prepare($query_string);

        if ($statementHandler !== false) {

            $encrypted_password = md5($password_param);

            $statementHandler->bindParam(':firstname', $firstname_param);
            $statementHandler->bindParam(':lastname', $lastname_param);
            $statementHandler->bindParam(':email', $email_param);
            $statementHandler->bindParam(':username', $username_param);
            $statementHandler->bindParam(':password', $encrypted_password);

            $statementHandler->execute();


            $last_inserted_user_id = $this->database_handler->lastInsertId();

            $query_string = "SELECT id, username, email FROM users WHERE id=:last_user_id";
            $statementHandler = $this->database_handler->prepare($query_string);

            $statementHandler->bindParam(':last_user_id', $last_inserted_user_id);

            $statementHandler->execute();

            return $statementHandler->fetch();
        } else {
            return false;
        }
    }

    //kollar om username är tagen 

    private function isUsernameTaken($username_param)
    {

        $query_string = "SELECT COUNT(id) FROM users WHERE username=:username";
        $statementHandler = $this->database_handler->prepare($query_string);

        if ($statementHandler !== false) {

            $statementHandler->bindParam(":username", $username_param);
            $statementHandler->execute();

            $numberOfUsernames = $statementHandler->fetch()[0];

            if ($numberOfUsernames > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            echo "Statementhandler epic fail!";
            die;
        }
    }

    // kollar om email är taken

    private function isEmailTaken($email_param)
    {
        $query_string = "SELECT COUNT(id) FROM users WHERE email=:email";
        $statementHandler = $this->database_handler->prepare($query_string);

        if ($statementHandler !== false) {

            $statementHandler->bindParam(":email", $email_param);
            $statementHandler->execute();

            $numberOfUsers = $statementHandler->fetch()[0];

            if ($numberOfUsers > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            echo "Statementhandler epic fail!";
            die;
        }
    }

    //loginin in user med username och password

    public function loginUser($username_parameter, $password_parameter)
    {
        $return_object = new stdClass();

        $query_string = "SELECT id, username, email FROM users WHERE username=:username_IN AND password=:password_IN";
        $statementHandler = $this->database_handler->prepare($query_string);

        if ($statementHandler !== false) {

            $password = md5($password_parameter);

            $statementHandler->bindParam(':username_IN', $username_parameter);
            $statementHandler->bindParam(':password_IN', $password);



            $statementHandler->execute();
            $return = $statementHandler->fetch();

            if (!empty($return)) {

                $this->username = $return['username'];

                $return_object->token = $this->getToken($return['id'], $return['username']);

                   return (json_encode($return_object));
                
            } else {
                echo "fel login";
            }
        } else {
            echo "Could not create a statementhandler";
            die;
        }
    }

    // kollar om user har en token om inte så körs createToken functionen

    private function getToken($userID, $username)
    {

        $token = $this->checkToken($userID);

        return $token;
    }

    private function checkToken($userID_IN)
    {

        $query_string = "SELECT token, token_update FROM token WHERE userid=:userID";
        $statementHandler = $this->database_handler->prepare($query_string);

        if ($statementHandler !== false) {

            $statementHandler->bindParam(":userID", $userID_IN);
            $statementHandler->execute();
            $return = $statementHandler->fetch();



            if (!empty($return['token'])) {
                // token finns

                $token_timestamp = $return['token_update'];
                $diff = time() - $token_timestamp;
                if (($diff / 60) > $this->token_validity_time) {

                    $query_string = "DELETE FROM token WHERE userid=:userID";
                    $statementHandler = $this->database_handler->prepare($query_string);

                    $statementHandler->bindParam(':userID', $userID_IN);
                    $statementHandler->execute();

                    return $this->createToken($userID_IN);
                } else {
                    return $return['token'];
                }
            } else {

                return $this->createToken($userID_IN);
            }
        } else {
            echo "Could not create a statementhandler";
        }
    }

    // tilldelar en token till user

    private function createToken($user_id_parameter)
    {

        $uniqToken = md5($this->username . uniqid('', true) . time());

        $query_string = "INSERT INTO token (userid, token, token_update) VALUES(:userid, :token, :current_time)";
        $statementHandler = $this->database_handler->prepare($query_string);

        if ($statementHandler !== false) {

            $currentTime = time();
            $statementHandler->bindParam(":userid", $user_id_parameter);
            $statementHandler->bindParam(":token", $uniqToken);
            $statementHandler->bindParam(":current_time", $currentTime, PDO::PARAM_INT);

            $statementHandler->execute();

            return $uniqToken;
        } else {
            return "Could not create a statementhandler";
        }
    }

    // kollar om token är godkänd innom en viss tids gräns 

    public function validateToken($token)
    {

        $query_string = "SELECT userid, token_update FROM token WHERE token=:token";
        $statementHandler = $this->database_handler->prepare($query_string);

        if ($statementHandler !== false) {

            $statementHandler->bindParam(":token", $token);
            $statementHandler->execute();

            $token_data = $statementHandler->fetch();

            if (!empty($token_data['token_update'])) {

                $diff = time() - $token_data['token_update'];

                if (($diff / 60) < $this->token_validity_time) {

                    $query_string = "UPDATE token SET token_update=:updated_date WHERE token=:token";
                    $statementHandler = $this->database_handler->prepare($query_string);

                    $updatedDate = time();
                    $statementHandler->bindParam(":updated_date", $updatedDate, PDO::PARAM_INT);
                    $statementHandler->bindParam(":token", $token);

                    $statementHandler->execute();

                    return true;
                } else {
                    echo "Session closed due to inactivity<br />";
                    return false;
                }
            } else {
                echo "Could not find token, please login first<br />";
                return false;
            }
        } else {
            echo "Couldnt create statementhandler<br />";
            return false;
        }

        return true;
    }

    //hämtar user ID med token 

    private function getUserId($token)
    {
        $query_string = "SELECT userid FROM token WHERE token=:token";
        $statementHandler = $this->database_handler->prepare($query_string);

        if ($statementHandler !== false) {

            $statementHandler->bindParam(":token", $token);
            $statementHandler->execute();

            $return = $statementHandler->fetch()[0];

            if (!empty($return)) {
                return $return;
            } else {
                return -1;
            }
        } else {
            echo "Couldn't create a statementhandler!";
        }
    }

    //hämtar user info 

    private function getUserData($userID) {

        $query_string = "SELECT id, username, email, role FROM users WHERE id=:userID_IN";
        $statementHandler = $this->database_handler->prepare($query_string);

        if($statementHandler !== false) {

            $statementHandler->bindParam(":userID_IN", $userID);
            $statementHandler->execute();
            
            $return = $statementHandler->fetch();

            if(!empty($return)) {
                return $return;
            } else {
                return false;
            }

        } else {
            echo "Couldn't create statement handler!";
        }

    }

    // function som kolla om man är admin

    public function isAdmin($token)
    {
        $user_id = $this->getUserId($token);
        $user_data = $this->getUserData($user_id);

        if($user_data['role'] == 1) {
            return true;
        } else {
            return false;
        }

    }

}



?>
