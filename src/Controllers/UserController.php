<?php

namespace App\Controllers;

class UserController extends Controller
{
    private $email;
    private $password;
    private $firstName;
    private $lastName;
    private $identifier;

    function __construct($db, $twig, $mail, $rlib)
    {
        parent::__construct($db, $twig, $mail, $rlib);
    }

    function login($params)
    {
        $this->email = trim($params['email']);
        $this->password = trim($params['password']);

        try {
            $stmt = $this->db->prepare("SELECT * FROM project.users WHERE email=:userEmail");
            $stmt->bindParam("userEmail", $this->email, \PDO::PARAM_STR);
            $stmt->execute();
            $count = $stmt->rowCount();
            $user = $stmt->fetch(\PDO::FETCH_OBJ);

            if ($count) { //if user found
                if (password_verify($this->password, $user->password)) { //if password is correct

                    unset($user->password); //delete password from data

                    $_SESSION['user'] = $user; // Storing user session value

                    $this->db = null;

                    return array(
                        'success' => true,
                        'msgTitle' => 'Logged in..',
                        'msgBody' => 'Redirecting to the homepage...'
                    );

                } else { //if password incorrect
                    return array(
                        'success' => false,
                        'msgTitle' => 'Incorrect password',
                        'msgBody' => 'Password incorrect. Please try again.'
                    );
                }
            } else { //if user not found
                return array(
                    'success' => false,
                    'msgTitle' => 'User does not exist',
                    'msgBody' => 'Sorry, account associated to that email address.'
                );
            }
        } catch (\PDOException $e) {
            return array(
                'success' => false,
                'msgTitle' => 'Error',
                'msgBody' => $e->getMessage()
            );
        }
    }

    function register($params)
    {
        $this->email = trim($params['email']);
        $this->password = trim($params['inputPassword']);
        $this->firstName = trim($params['firstName']);
        $this->lastName = trim($params['lastName']);

        try {
            $stmt = $this->db->prepare("SELECT COUNT(email) AS num FROM project.users WHERE email = :userEmail");
            $stmt->bindParam("userEmail", $this->email, \PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row['num'] > 0) {//user already exists
                return array(
                    'success' => false,
                    'msgTitle' => 'Account already exists.',
                    'msgBody' => 'This email address is already registered to an account.'
                );
            } else {

                if (strlen($this->password) <= 7) {
                    return array(
                        'success' => false,
                        'msgTitle' => 'Check password',
                        'msgBody' => 'Please check your password length meets the required password complexity.'
                    );
                }
                if (!preg_match('#[0-9]+#', $this->password)) {
                    return array('success' => false,
                        'msgTitle' => 'Check password',
                        'msgBody' => 'Please check your password contains at least one number'
                    );
                }
                if (!preg_match('#[a-z]+#', $this->password)) {
                    return array('success' => false,
                        'msgTitle' => 'Check password',
                        'msgBody' => 'Please check your password contains at least one lowercase letter.'
                    );
                }
                if (!preg_match('#[A-Z]+#', $this->password)) {
                    return array('success' => false,
                        'msgTitle' => 'Check password',
                        'msgBody' => 'Please check your password contains at least one uppercase letter.'
                    );
                }

                $hash_password = password_hash($this->password, PASSWORD_BCRYPT);

                $stmt = $this->db->prepare("INSERT INTO project.users (first_name, last_name, email, password) VALUES (:first_name, :last_name, :email, :password)");
                $stmt->bindParam("first_name", $this->firstName, \PDO::PARAM_STR);
                $stmt->bindParam("last_name", $this->lastName, \PDO::PARAM_STR);
                $stmt->bindParam("email", $this->email, \PDO::PARAM_STR);
                $stmt->bindParam("password", $hash_password, \PDO::PARAM_STR);

                $result = $stmt->execute();

                if ($result) {
                    $this->mail->send('emails/registered.twig', ['firstName' => $this->firstName, 'lastName' => $this->lastName],
                        function ($message) {
                            $message->to($this->email);
                            $message->subject('Registered successfully!');
                        });

                    return array( //registered successfully
                        'success' => true,
                        'msgTitle' => 'Registration successful!',
                        'msgBody' => 'Thanks! Your account has been registered successfully. You can now login from the home page.'
                    );
                } else {
                    return array( //registered successfully
                        'success' => false,
                        'msgTitle' => 'O shit',
                        'msgBody' => 'Something pretty bad went wrong ngl'
                    );
                }
            }

        } catch (\PDOException $e) {
            return array(
                'success' => false,
                'msgTitle' => 'Error',
                'msgBody' => $e->getMessage()
            );
        } catch (\phpmailerException $e) {
            return array( //PHPMailler error
                'success' => false,
                'msgTitle' => 'Error',
                'msgBody' => $e->errorMessage()
            );
        }

    }

    function verifyUser($formID)
    {
        try { //first check if form is private or not, if not, allow it to be viewed
            $stmt = $this->db->prepare("SELECT private FROM project.forms WHERE ID = :formID");
            $stmt->bindParam("formID", $formID, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_OBJ);

            if ($row->private == 0) { //if the form is public anyway..
                return true;
            } else {//else if its private, check the user logged it should be able to access it
                $stmt = $this->db->prepare("SELECT COUNT(user_id) AS num FROM project.permissions WHERE form_ID = :formID AND user_ID = :userID");
                $stmt->bindParam("formID", $formID, \PDO::PARAM_INT);
                $stmt->bindParam("userID", $_SESSION['user']->id, \PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($row['num'] > 0) {
                    return true;
                } else {
                    return false;
                }
            }
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }

    function setRecoveryHash($params)
    {
        $this->email = trim($params['email']);

        try {
            $stmt = $this->db->prepare("SELECT COUNT(email) AS num FROM project.users WHERE email = :userEmail");
            $stmt->bindParam("userEmail", $this->email, \PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            return array( //PDO error
                'success' => false,
                'msgTitle' => 'Database error',
                'msgBody' => $e->getMessage()
            );
        }

        if ($row['num'] > 0) { //if user is found by email

            $identifier = $this->rlib->generateString(128);
            $hashedIdentifier = password_hash($identifier, PASSWORD_BCRYPT);

            try {
                $stmt = $this->db->prepare("UPDATE project.users SET recover_pwd_hash = :recoverHash WHERE email = :userEmail");
                $stmt->bindParam("recoverHash", $hashedIdentifier, \PDO::PARAM_STR);
                $stmt->bindParam("userEmail", $this->email, \PDO::PARAM_STR);
                $stmt->execute();

                $emailSuccess = $this->mail->send('emails/passwordreset.twig', ['identifier' => $identifier, 'email' => $this->email],
                    function ($message) {
                        $message->to($this->email);
                        $message->subject('Finish resetting your password');
                    });

            } catch (\PDOException $e) {
                return array( //PDO error
                    'success' => false,
                    'msgTitle' => 'Database error',
                    'msgBody' => $e->getMessage()
                );
            } catch (\phpmailerException $e) {
                return array( //PHPMailler error
                    'success' => false,
                    'msgTitle' => 'Error',
                    'msgBody' => $e->errorMessage()
                );
            }

            if (is_bool($emailSuccess) && ($emailSuccess)) {
                return array( //Successful
                    'success' => true,
                    'msgTitle' => 'Email sent!',
                    'msgBody' => 'An email has been sent to your email address with further instructions.'
                );
            }
        } else {
            return array( //user not found
                'success' => false,
                'msgTitle' => 'Unrecognised email address.',
                'msgBody' => 'Sorry, that email address isn\'t associated to any account.'
            );
        }
    }

    function checkResetPassword($params)
    {
        $this->email = $params['email'];
        $this->identifier = $params['identifier'];

        try {
            $stmt = $this->db->prepare("SELECT * FROM project.users WHERE email = :userEmail");
            $stmt->bindParam("userEmail", $this->email, \PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            die('Database error: ' . $e->getMessage());
        }

        if (($user) && password_verify($this->identifier, $user->recover_pwd_hash)) {
            return true;
        } else {
            return false;
        }
    }

    function resetPassword($params)
    {
        $this->email = $params['email'];
        $this->identifier = $params['identifier'];
        $newPassword = $params['password'];
        $confirmPassword = $params['confirmPassword'];

        try {
            $stmt = $this->db->prepare("SELECT * FROM project.users WHERE email = :userEmail");
            $stmt->bindParam("userEmail", $this->email, \PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            return array( //PDO error
                'success' => false,
                'msgTitle' => 'Database error',
                'msgBody' => $e->getMessage()
            );
        }

        if (($user) && password_verify($this->identifier, $user->recover_pwd_hash)) {

            if ($newPassword == $confirmPassword) {

                if (strlen($newPassword) <= 7) {
                    return array('success' => false,
                        'msgTitle' => 'Check password',
                        'msgBody' => 'Please check your password length meets the required password complexity.'
                    );
                }
                if (!preg_match('#[0-9]+#', $newPassword)) {
                    return array('success' => false,
                        'msgTitle' => 'Check password',
                        'msgBody' => 'Please check your password contains at least one number'
                    );
                }
                if (!preg_match('#[a-z]+#', $newPassword)) {
                    return array('success' => false,
                        'msgTitle' => 'Check password',
                        'msgBody' => 'Please check your password contains at least one lowercase letter.'
                    );
                }
                if (!preg_match('#[A-Z]+#', $newPassword)) {
                    return array('success' => false,
                        'msgTitle' => 'Check password',
                        'msgBody' => 'Please check your password contains at least one uppercase letter.'
                    );
                }

                $hash_password = password_hash($newPassword, PASSWORD_BCRYPT);

                try {
                    $stmtPwd = $this->db->prepare("UPDATE project.users SET password = :password, recover_pwd_hash = NULL WHERE email = :email");
                    $stmtPwd->bindParam("password", $hash_password, \PDO::PARAM_STR);
                    $stmtPwd->bindParam("email", $this->email, \PDO::PARAM_STR);
                    $stmtPwd->execute();

                    $emailSuccess = $this->mail->send('emails/passwordchanged.twig', ['' => ''],
                        function ($message) {
                            $message->to($this->email);
                            $message->subject('Password reset successfully');
                        });
                } catch (\PDOException $e) {
                    return array(
                        'success' => false,
                        'msgTitle' => 'Error',
                        'msgBody' => $e->getMessage()
                    );
                } catch (\phpmailerException $e) {
                    return array( //PHPMailler error
                        'success' => false,
                        'msgTitle' => 'Error',
                        'msgBody' => $e->errorMessage()
                    );
                }

                if (is_bool($emailSuccess) && ($emailSuccess)) {
                    return array( //registered successfully
                        'success' => true,
                        'msgTitle' => 'Success',
                        'msgBody' => 'Thanks. Your password has been reset successfully. You can now login from the home page.'
                    );
                }
            } else {
                return array( //success
                    'success' => false,
                    'msgTitle' => 'Passwords do not match',
                    'msgBody' => 'Please check that your new passwords match, and try again.'
                );
            }
        } else {
            return array( //hash doesn't match DB
                'success' => false,
                'msgTitle' => 'Authorisation problem.',
                'msgBody' => 'Sorry, there\'s a problem authenticating your request. Please try again'
            );
        }
    }

    function changePassword($params)
    {
        $this->password = trim($params['currentPassword']);
        $this->email = trim($params['userEmail']);
        $newPassword = trim($params['newPassword']);
        $confirmNewPassword = trim($params['confirmNewPassword']);

        try {
            $stmt = $this->db->prepare("SELECT email, password FROM project.users WHERE email = :userEmail LIMIT 1");
            $stmt->bindParam("userEmail", $this->email, \PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            return array(
                'success' => false,
                'msgTitle' => 'Database error',
                'msgBody' => $e->getMessage()
            );
        }

        if (($user) && password_verify($this->password, $user->password)) {

            if ($newPassword == $confirmNewPassword) {

                if (strlen($newPassword) <= 7) {
                    return array(
                        'success' => false,
                        'msgTitle' => 'Check new password',
                        'msgBody' => 'Please check your password length meets the password length requirement.'
                    );
                }
                if (!preg_match('#[0-9]+#', $newPassword)) {
                    return array(
                        'success' => false,
                        'msgTitle' => 'Check new password',
                        'msgBody' => 'Please check your password contains at least one number'
                    );
                }
                if (!preg_match('#[a-z]+#', $newPassword)) {
                    return array(
                        'success' => false,
                        'msgTitle' => 'Check new password',
                        'msgBody' => 'Please check your password contains at least one lowercase letter.'
                    );
                }
                if (!preg_match('#[A-Z]+#', $newPassword)) {
                    return array(
                        'success' => false,
                        'msgTitle' => 'Check new password',
                        'msgBody' => 'Please check your password contains at least one uppercase letter.'
                    );
                }

                $hash_password = password_hash($newPassword, PASSWORD_BCRYPT);

                try {
                    $stmtPwd = $this->db->prepare("UPDATE project.users SET password = :password WHERE email = :email");
                    $stmtPwd->bindParam("password", $hash_password, \PDO::PARAM_STR);
                    $stmtPwd->bindParam("email", $this->email, \PDO::PARAM_STR);
                    $stmtPwd->execute();

                    $emailSuccess = $this->mail->send('emails/passwordchanged.twig', ['' => ''],
                        function ($message) {
                            $message->to($this->email);
                            $message->subject('Password changed successfully');
                        });
                } catch (\PDOException $e) {
                    return array(
                        'success' => false,
                        'msgTitle' => 'Error',
                        'msgBody' => $e->getMessage()
                    );
                } catch (\phpmailerException $e) {
                    return array( //PHPMailler error
                        'success' => false,
                        'msgTitle' => 'Error',
                        'msgBody' => $e->errorMessage()
                    );
                }

                if (is_bool($emailSuccess) && ($emailSuccess)) {
                    return array( //pw changed successfully
                        'success' => true,
                        'msgTitle' => 'Success!',
                        'msgBody' => 'Thanks! Your password has been changed successfully.'
                    );
                }
            } else {
                return array(
                    'success' => false,
                    'msgTitle' => 'Passwords don\'t match',
                    'msgBody' => 'Please check that your new passwords match.'
                );
            }
        } else {
            return array(
                'success' => false,
                'msgTitle' => 'Incorrect password',
                'msgBody' => 'Please check that your current password is correct, and try again.'
            );
        }
    }

}