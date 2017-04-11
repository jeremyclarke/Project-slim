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

$app->post('/login', function ($request, $response, $args) use ($app) {
    $userController = $this->UserController;
    $isUserLoggedIn = $userController->loginUser($request->getParams());

    if (is_bool($isUserLoggedIn)) {
        if ($isUserLoggedIn) {
            return $response->withJson(
                array(
                    'success' => true,
                    'msgTitle' => 'Logged in.',
                    'msgBody' => 'Redirecting to the home page...'
                ));
        } else if (!$isUserLoggedIn) {
            return $response->withJson(
                array('success' => false,
                    'msgBody' => 'Email address or password incorrect. Please try again.'
                ));
        }
    } else {
        return $response->withJson(array(
            'success' => false,
            'msgBody' => 'Error: ' . $isUserLoggedIn));
    }

})->setName('login');


$app->post('/register', function ($request, $response, $args) {
    $userController = $this->UserController;
    $isRegisterSuccessful = $userController->registerUser($request->getParams());

    if (is_bool($isRegisterSuccessful)) {
        if ($isRegisterSuccessful) {
            return $response->withJson(
                array(
                    'success' => true,
                    'msgTitle' => 'Registration successful!',
                    'msgBody' => 'Thanks! Your account has been registered successfully. You can now login from the home page.'
                ));
        } else if (!$isRegisterSuccessful) {
            return $response->withJson(
                array('success' => false,
                    'msgTitle' => 'User already exists',
                    'msgBody' => 'A user with this email address already exists. Please try again.'
                ));
        }
    } else {
        return $response->withJson(array(
            'success' => false,
            'msgTitle' => 'Error',
            'msgBody' => 'Details: ' . $isRegisterSuccessful));
    }
})->setName('register');


$app->get('/logout', function ($request, $response, $args) {
    session_start();
    unset($_SESSION["user"]);
    return $response->withStatus(302)->withHeader('Location', '/');
})->setName('logout');


$app->get('/admin', function ($request, $response, $args) {
    echo 'admin panel here';
})->setName('admin');


$app->post('/forgot-password', function ($request, $response, $args) {
    $userController = $this->UserController;
    $isResetSuccessful = $userController->startResetPassword($request->getParams());

    if (is_bool($isResetSuccessful)) {
        if ($isResetSuccessful) {
            return $response->withJson(array('success' => true, 'msgTitle' => 'Email sent!', 'msgBody' => 'Thanks! An email has been sent to your email address with further instructions.'));
        } else if (!$isResetSuccessful) {
            return $response->withJson(array('success' => false, 'msgTitle' => 'User does not exist.', 'msgBody' => 'No user exists with that email address. Please try again.'));
        }
    } else {
        return $response->withJson(array('success' => false, 'msgTitle' => 'Error', 'msgBody' => 'Details: ' . $isResetSuccessful));
    }
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

    echo 'poste';
//    return $this->twig->render($response, 'resetpassword.twig',
//        [
////            'formsAll' => $formController->returnAllFormDetails(),
////            'formsAllPrivate' => $formController->returnUsersPrivateFormDetails($_SESSION['user']->id), //private forms
////            'user' => $_SESSION['user']
//        ]
//    );
})->setName('resetPasswordPost');




//$app->get('/signup', function ($request, $response, $args) {
//    $formController = new \App\Controllers\FormController($this->db);
//
//    return $this->twig->render($response, 'signup.twig', [
//        'formsAll' => $formController->returnAllFormDetails()
//    ]);
//})->setName('signup');