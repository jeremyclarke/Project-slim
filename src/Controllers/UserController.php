<?php

namespace App\Controllers;


class UserController
{
    private $dbconn;
    private $email;
    private $password;
    private $firstName;
    private $lastName;

    function __construct($db)
    {
        $this->dbconn = $db;
    }

    function loginUser($params)
    {
        $this->email = trim($params['email']);
        $this->password = trim($params['password']);

        try {
            //$hash_password = password_hash($this->password, PASSWORD_BCRYPT);
            $stmt = $this->dbconn->prepare("SELECT * FROM project.users WHERE email=:userEmail");;// AND password=:hash_password");
            $stmt->bindParam("userEmail", $this->email, \PDO::PARAM_STR);
            //$stmt->bindParam("hash_password", $hash_password, \PDO::PARAM_STR);
            $stmt->execute();
            $count = $stmt->rowCount();
            $data = $stmt->fetch(\PDO::FETCH_OBJ);
            //$this->dbconn = null;

            if ($count) { //if user found
                if (password_verify($this->password, $data->password)) { //if password is correct
                    unset($data->password);
                    $_SESSION['user'] = $data; // Storing user session value
                    return true;
                } else { //if password incorrect
                    return false;
                }
            } else { //if user not found
                return false;
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    function registerUser($params)
    {
        $this->email = trim($params['email']);
        $this->password = trim($params['password']);
        $this->firstName = trim($params['firstName']);
        $this->lastName = trim($params['lastName']);

        try {
            $stmt = $this->dbconn->prepare("SELECT COUNT(email) AS num FROM project.users WHERE email = :userEmail");
            $stmt->bindParam("userEmail", $this->email, \PDO::PARAM_STR);

            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row['num'] > 0) {
                die('User already exists');
            }

            $hash_password = password_hash($this->password, PASSWORD_BCRYPT);

            $stmt = $this->dbconn->prepare("INSERT INTO project.users (first_name, last_name, email, password) VALUES (:first_name, :last_name, :email, :password)");
            $stmt->bindParam("first_name", $this->firstName, \PDO::PARAM_STR);
            $stmt->bindParam("last_name", $this->lastName, \PDO::PARAM_STR);
            $stmt->bindParam("email", $this->email, \PDO::PARAM_STR);
            $stmt->bindParam("password", $hash_password, \PDO::PARAM_STR);

            $result = $stmt->execute();

            if ($result) {
                echo 'Thank you for registering.';
                die();
            }

        } catch (\PDOException $e) {
            echo $e->getMessage();

        }


    }
}