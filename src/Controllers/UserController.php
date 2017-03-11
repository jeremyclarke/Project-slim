<?php

namespace App\Controllers;


class UserController
{
    private $dbconn;
    private $email;
    private $password;

    function __construct($db, $params)
    {
        $this->dbconn = $db;
        $this->email = $params['email'];
        $this->password = $params['password'];

        $this->loginUser();
    }

    function loginUser()
    {
        try {
            //$hash_password = password_hash($this->params('password'), PASSWORD_BCRYPT);
            //$hash_password = $this->password;
            $stmt = $this->dbconn->prepare("SELECT id FROM project.users WHERE email=:usernameEmail AND password=:hash_password");
            $stmt->bindParam("usernameEmail", $this->email, \PDO::PARAM_STR);
            $stmt->bindParam("hash_password", $this->password, \PDO::PARAM_STR);
            $stmt->execute();
            $count = $stmt->rowCount();
            $data = $stmt->fetch(\PDO::FETCH_OBJ);
            //$this->dbconn = null;
            if ($count) {
                $_SESSION['id'] = $data->id; // Storing user session value
                //return true;
                echo 'yaaay';
            } else {
               // return false;
                echo 'naaay';
            }
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }
}