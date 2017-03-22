<?php
// Routes

$app->get('/', function ($request, $response, $args) {

    //if (isset($_COOKIE['displayUnauthAccess'])) {
    echo $_COOKIE["displayUnauthAccess"];    //}

    echo 'y';

    die();

    $formController = new \App\Controllers\FormController($this->db);


    if (isset($_SESSION['user'])) {
        return $this->twig->render($response, 'homepage.twig', [
            'formsAllPublic' => $formController->returnPublicFormDetails(), //public forms
            'formsAllPrivate' => $formController->returnUsersPrivateFormDetails($_SESSION['user']->id), //private forms
            'user' => $_SESSION['user']
        ]);
    } else {
        return $this->twig->render($response, 'homepage.twig', [
            'formsAllPublic' => $formController->returnPublicFormDetails(), //public forms
            'user' => $_SESSION['user']
        ]);
    }
})->
setName('home');

$app->get('/form/{id}', function ($request, $response, $args) {

    $formController = new \App\Controllers\FormController($this->db);
    $objectController = new \App\Controllers\ObjectController($this->db);
    $userController = new \App\Controllers\UserController($this->db);

    if ($userController->verifyUser($args['id'])) {
        return $this->twig->render($response, 'form.twig', [
            'formsAllPublic' => $formController->returnPublicFormDetails(),
            'formDetails' => $formController->returnPublicFormDetails()[$args['id']],
            'objectsAll' => $objectController->returnAllFormObjects($args['id']),
            'objectSQL' => $objectController->getStatementResults(),
            'user' => $_SESSION['user']
        ]);
    } else {

        //echo $_COOKIE["displayUnauthAccess"];

        return $response->withStatus(302)->withHeader('Location', '/');
        setcookie("displayUnauthAccess", "Unauthorised");

        exit;
    }
})->setName('form');

$app->post('/submit/{ID}', function ($request, $response, $args) {
    $formController = new \App\Controllers\FormController($this->db);
    $msg = $formController->submitForm($request->getParams(), $args['ID']);

    return $response->withJson(array('msg' => $msg));
})->setName('formInsert');

$app->post('/login', function ($request, $response, $args) use ($app) {

    $userController = new \App\Controllers\UserController($this->db);
    $isUserLoggedIn = $userController->loginUser($request->getParams());

    if ($isUserLoggedIn) {
        //do nothing
    } else if (!$isUserLoggedIn) {
        return $response->withJson(array('msg' => 'Incorrect email address or password. Please try again.'));
    } else {
        return $response->withJson(array('msg' => 'Error: ' . $isUserLoggedIn));
    }

})->setName('login');

$app->get('/signup', function ($request, $response, $args) {
    $formController = new \App\Controllers\FormController($this->db);

    return $this->twig->render($response, 'signup.twig', [
        'formsAll' => $formController->returnAllFormDetails()
    ]);
})->setName('signup');

$app->post('/register', function ($request, $response, $args) {
    $userController = new \App\Controllers\UserController($this->db);
    $registerUser = $userController->registerUser($request->getParams());

    die();

//    return $this->twig->render($response, 'signup.twig', [
//        'formsAll' => $formController->returnAllFormDetails()
//    ]);
})->setName('register');

$app->get('/logout', function ($request, $response, $args) {

    session_start();
    unset($_SESSION["user"]);
    return $response->withStatus(302)->withHeader('Location', '/');
})->setName('logout');