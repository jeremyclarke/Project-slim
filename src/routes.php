<?php
// Routes

$app->get('/', function ($request, $response, $args) {
    $formController = $this->FormController;

    if (isset($_SESSION['user'])) { //If user is logged in
        return $this->twig->render($response, 'homepage.twig',
            [
                'formsAll' => $formController->returnAllFormDetails(),
                'formsAllPrivate' => $formController->returnUsersPrivateFormDetails($_SESSION['user']->id), //private forms
                'user' => $_SESSION['user']
            ]
        );
    } else {
        return $this->twig->render($response, 'homepage.twig',
            [
                'formsAll' => $formController->returnAllFormDetails() //public forms
            ]
        );
    }
})->setName('home');


$app->get('/form/{id}', function ($request, $response, $args) {
    $formController = $this->FormController;
    $objectController = $this->ObjectController;
    $userController = $this->UserController;

    if ($userController->verifyUser($args['id'])) {
        return $this->twig->render
        ($response, 'form.twig',
            [
                //'formsAll' => $formController->returnAllFormDetails(),
                'formDetails' => $formController->returnAllFormDetails()[$args['id']],
                'objectsAll' => $objectController->returnAllFormObjects($args['id']),
                'objectSQL' => $objectController->getStatementResults(),
                'user' => $_SESSION['user']
            ]
        );
    } else {
        return $response->withStatus(302)->withHeader('Location', '/');
        setcookie("displayUnauthAccess", "Unauthorised");
        //exit;
    }
//    }
})->setName('form');


$app->post('/submit/{ID}', function ($request, $response, $args) {
    $formController = $this->FormController;
    $msg = $formController->submitForm($request->getParams(), $args['ID']);

    return $response->withJson(array('msg' => $msg));
})->setName('formInsert');


// ** LOGIN **
$app->post('/login', function ($request, $response, $args) {
    $userController = $this->UserController;
    $checkLogin = $userController->login($request->getParams());
    return $response->withJson($checkLogin);
})->setName('login');
////////////////////////////////////////////////////////////////

// ** LOGOUT **
$app->get('/logout', function ($request, $response, $args) {
    session_start();
    unset($_SESSION["user"]);
    return $response->withStatus(302)->withHeader('Location', '/');
})->setName('logout');


// ** REGISTER **
$app->post('/register', function ($request, $response, $args) {
    $userController = $this->UserController;
    $checkRegister = $userController->register($request->getParams());
    return $response->withJson($checkRegister);
})->setName('register');


$app->get('/admin', function ($request, $response, $args) {
    echo 'admin panel here';
})->setName('admin');


// ** FORGOT PASSWORD **
$app->post('/forgot-password', function ($request, $response, $args) {
    $userController = $this->UserController;
    $checkRecovery = $userController->setRecoveryHash($request->getParams());
    return $response->withJson($checkRecovery);
})->setName('forgotPassword');












$app->get('/password-reset', function ($request, $response, $args) {
    //$userController =  $this->UserController;
    $formController = $this->FormController;
//    $isResetSuccessful = $this->UserController->finishResetPassword($request->getParams());

    $email = $request->getParams()['email'];
    $identifier = $request->getParams()['identifier'];

    $stmt = $this->db->prepare("SELECT * FROM project.users WHERE email = :userEmail");// AND recover_pwd_hash = :identifier LIMIT 1");
    $stmt->bindParam("userEmail", $email, \PDO::PARAM_STR);
    //$stmt->bindParam("identifier", $identifier, \PDO::PARAM_STR);

    $stmt->execute();
    $user = $stmt->fetch(\PDO::FETCH_OBJ);

    if (($user) && password_verify($identifier, $user->recover_pwd_hash)) {

        return $this->twig->render($response, 'resetpassword.twig',
            [
                'formsAll' => $formController->returnAllFormDetails(),
                'formsAllPrivate' => $formController->returnUsersPrivateFormDetails($_SESSION['user']->id), //private forms
                'user' => $_SESSION['user'],
                'email' => $email,
                'identifier' => $identifier
            ]
        );

    } else {
        die('na');
    }

})->setName('resetPassword');

$app->post('/password-reset', function ($request, $response, $args) {

    $email = $request->getParams()['email'];
    $identifier = $request->getParams()['identifier'];
    $password = $request->getParams()['password'];

    $stmt = $this->db->prepare("SELECT * FROM project.users WHERE email = :userEmail");// AND recover_pwd_hash = :identifier LIMIT 1");
    $stmt->bindParam("userEmail", $email, \PDO::PARAM_STR);
    //$stmt->bindParam("identifier", $identifier, \PDO::PARAM_STR);

    $stmt->execute();
    $user = $stmt->fetch(\PDO::FETCH_OBJ);

    if (($user) && password_verify($identifier, $user->recover_pwd_hash)) {

        $hash_password = password_hash($password, PASSWORD_BCRYPT);

        $stmtPwd = $this->db->prepare("UPDATE project.users SET password = :password WHERE email = :email");
        $stmtPwd->bindParam("password", $hash_password, \PDO::PARAM_STR);
        $stmtPwd->bindParam("email", $email, \PDO::PARAM_STR);

        $result = $stmtPwd->execute();

        if ($result) {
            die('done');
            return true; //registered successfully
        }
    } else {
        die('na');

    }
//    return $this->twig->render($response, 'resetpassword.twig',
//        [
////            'formsAll' => $formController->returnAllFormDetails(),
////            'formsAllPrivate' => $formController->returnUsersPrivateFormDetails($_SESSION['user']->id), //private forms
////            'user' => $_SESSION['user']
//        ]
//    );
})->setName('resetPasswordPost');


$app->post('/change-password', function ($request, $response, $args) {
    $userController = $this->UserController;
    $isChangePwdSuccessful = $userController->changePassword($request->getParams());

    if (is_bool($isChangePwdSuccessful)) {
        if ($isChangePwdSuccessful) {
            return $response->withJson(array('success' => true, 'msgTitle' => 'Password changed.', 'msgBody' => 'Your password has been changed successfully.'));
        } else if (!$isChangePwdSuccessful) {
            return $response->withJson(array('success' => false, 'msgTitle' => 'Something went wrong', 'msgBody' => 'Please check that your new password meets the requirements, and that your current password is correct.'));
        }
    } else {
        return $response->withJson(array('success' => false, 'msgTitle' => 'Error', 'msgBody' => 'Details: ' . $isChangePwdSuccessful));
    }
})->setName('changePassword');