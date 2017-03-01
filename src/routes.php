<?php
// Routes

$app->get('/', function ($request, $response, $args) {
    $formController = new \App\Controllers\FormController($this->db);

    return $this->twig->render($response, 'homepage.twig', [
        'formsAll' => $formController->returnAllFormDetails(),
        'homeButtonTitle' => 'Home'
    ]);
});

$app->get('/form/{id}', function ($request, $response, $args) {
    $formController = new \App\Controllers\FormController($this->db);
    $objectController = new \App\Controllers\ObjectController($this->db);

    return $this->twig->render($response, 'form.twig', [
        'formsAll' => $formController->returnAllFormDetails(),
        'formDetails' => $formController->returnAllFormDetails()[$args['id']],
        'objectsAll' => $objectController->returnAllFormObjects($args['id']),
        'objectSQL' => $objectController->getStatementResults()
    ]);

});

$app->post('/submit', function ($request, $response, $args) {

    $formController = new \App\Controllers\FormController($this->db);
    $formController->submitForm($request->getParams());

})->setName('formInsert');
