<?php
// Routes

$app->get('/', function ($request, $response, $args) {
    $formController = new \App\Controllers\FormController($this->db);

    return $this->twig->render($response, 'homepage.twig', [
        'formsAll' => $formController->returnAllFormDetails(),
        'homeButtonTitle' => 'Home'
    ]);
})->setName('home');

$app->get('/form/{id}', function ($request, $response, $args) {
    $formController = new \App\Controllers\FormController($this->db);
    $objectController = new \App\Controllers\ObjectController($this->db);

    return $this->twig->render($response, 'form.twig', [
        'formsAll' => $formController->returnAllFormDetails(),
        'formDetails' => $formController->returnAllFormDetails()[$args['id']],
        'objectsAll' => $objectController->returnAllFormObjects($args['id']),
        'objectSQL' => $objectController->getStatementResults()
    ]);


})->setName('form');

$app->post('/submit/{ID}', function ($request, $response, $args) {

    $formController = new \App\Controllers\FormController($this->db);
    $msg = $formController->submitForm($request->getParams(), $args['ID']);

    return $response->withJson(array('msg' => $msg));

})->setName('formInsert');
