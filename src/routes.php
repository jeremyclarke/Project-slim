<?php
// Routes

$app->get('/', function ($request, $response, $args) {
    $formController = new \App\Controllers\FormController($this->db);

    return $this->twig->render($response, 'homepage.twig', [
        'formsAll' => $formController->returnAllFormDetails(),
        'session' => $_SESSION

    ]);
})->setName('home');

$app->get('/form/{id}', function ($request, $response, $args) {
    $formController = new \App\Controllers\FormController($this->db);
    $objectController = new \App\Controllers\ObjectController($this->db);

    return $this->twig->render($response, 'form.twig', [
        'formsAll' => $formController->returnAllFormDetails(),
        'formDetails' => $formController->returnAllFormDetails()[$args['id']],
        'objectsAll' => $objectController->returnAllFormObjects($args['id']),
        'objectSQL' => $objectController->getStatementResults(),
        'session' => $_SESSION
    ]);
})->setName('form');

$app->post('/submit/{ID}', function ($request, $response, $args) {
    $formController = new \App\Controllers\FormController($this->db);
    $msg = $formController->submitForm($request->getParams(), $args['ID']);

    return $response->withJson(array('msg' => $msg));
})->setName('formInsert');

$app->post('/login', function ($request, $response, $args) use ($app) {

    $userController = new \App\Controllers\UserController($this->db);
    $isUserLoggedIn = $userController->loginUser($request->getParams());

    if ($isUserLoggedIn){
        //do nothing
    }
    else if (!$isUserLoggedIn) {
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

$app->get('/logout', function ($request, $response, $args) use ($app) {

    session_start();
    unset($_SESSION["user"]);
    //$app->redirect('/');
    return $response->withStatus(302)->withHeader('Location', '/');


})->setName('logout');