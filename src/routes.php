<?php
// Routes

$app->get('/', function ($request, $response, $args) {
    if (isset($_SESSION['user'])) { //If user is logged in
        return $this->twig->render($response, 'homepage.twig',
            [
                'forms' => $this->FormController->getForms(),
                'formsPrivate' => $this->FormController->getPrivateForms($_SESSION['user']->id) //private forms
            ]
        );
    } else {
        return $this->twig->render($response, 'homepage.twig',
            [
                'forms' => $this->FormController->getForms() //public forms
            ]
        );
    }
})->setName('home');


$app->get('/form/{id}', function ($request, $response, $args) {
    if ($this->UserController->verifyUser($args['id'])) {
        return $this->twig->render($response, 'form.twig',
            [
                'form' => $this->FormController->getSingleForm($args['id']),
                'objects' => $this->ObjectController->getFormObjects($args['id']),
                'objectSQL' => $this->ObjectController->getStatementResults(),
            ]
        );
    } else {
//        unset($_SESSION["user"]);
        return $response->withStatus(302)->withHeader('Location', '/'); //todo: error message upon return

    }
})->setName('form');


$app->post('/submit/{ID}', function ($request, $response, $args) {
    $checkSubmit = $this->FormController->submitForm($request->getParams(), $args['ID']);

    return $response->withJson($checkSubmit);
})->setName('formInsert');


// ** LOGIN **
$app->post('/login', function ($request, $response, $args) {
    $checkLogin = $this->UserController->login($request->getParams());
    return $response->withJson($checkLogin);
})->setName('login');


// ** LOGOUT **
$app->get('/logout', function ($request, $response, $args) {
    session_start();
    unset($_SESSION["user"]);
    return $response->withStatus(302)->withHeader('Location', '/');
})->setName('logout');


// ** REGISTER **
$app->post('/register', function ($request, $response, $args) {
    $checkRegister = $this->UserController->register($request->getParams());
    return $response->withJson($checkRegister);
})->setName('register');


$app->get('/admin', function (\Slim\Http\Request $request, \Slim\Http\Response $response, $args) {
    return $this->twig->render($response, 'admin.twig',
        [
//            'forms' => $this->FormController->getForms(),
//            'formsPrivate' => $this->FormController->getPrivateForms($_SESSION['user']->id) //private forms
        ]
    );
})->setName('admin');


// ** FORGOT PASSWORD **
$app->post('/forgot-password', function ($request, $response, $args) {
    $checkRecovery = $this->UserController->setRecoveryHash($request->getParams());
    return $response->withJson($checkRecovery);
})->setName('forgotPassword');


$app->get('/forgot-password', function ($request, $response, $args) {
    $checkReset = $this->UserController->checkResetPassword($request->getParams());
    if ($checkReset) {
        return $this->twig->render($response, 'resetpassword.twig',
            [
                'email' => $request->getParams()['email'],
                'identifier' => $request->getParams()['identifier']
            ]
        );
    } else {
        return $response->withStatus(302)->withHeader('Location', '/');
    }
})->setName('checkResetPassword');


$app->post('/reset-password', function ($request, $response, $args) {
    $checkReset = $this->UserController->resetPassword($request->getParams());
    return $response->withJson($checkReset);
})->setName('resetPasswordPost');

// ** CHANGE PASSWORD **
$app->post('/change-password', function ($request, $response, $args) {
    $checkRegister = $this->UserController->changePassword($request->getParams());
    return $response->withJson($checkRegister);
})->setName('changePassword');