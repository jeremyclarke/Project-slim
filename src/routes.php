<?php
// Routes

$app->get('/', function ($request, $response, $args) {

    $formController = new \App\Controllers\FormController($this->db);

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

//    $form = populateForm($args['id']);
//    //more code here
//
//    renderPage($response->$form);
//});

    $formController = new \App\Controllers\FormController($this->db);
    $objectController = new \App\Controllers\ObjectController($this->db);
    $userController = new \App\Controllers\UserController($this->db);

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
    $formController = new \App\Controllers\FormController($this->db);
    $msg = $formController->submitForm($request->getParams(), $args['ID']);

    return $response->withJson(array('msg' => $msg));
})->setName('formInsert');

$app->post('/login', function ($request, $response, $args) use ($app) {

    $userController = new \App\Controllers\UserController($this->db);
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
    $userController = new \App\Controllers\UserController($this->db);
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
    $userController = new \App\Controllers\UserController($this->db);
    $isResetSuccessful = $userController->resetPassword($request->getParams());

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




//$app->get('/signup', function ($request, $response, $args) {
//    $formController = new \App\Controllers\FormController($this->db);
//
//    return $this->twig->render($response, 'signup.twig', [
//        'formsAll' => $formController->returnAllFormDetails()
//    ]);
//})->setName('signup');