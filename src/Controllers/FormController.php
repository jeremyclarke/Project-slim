<?php

namespace App\Controllers;

class FormController
{
    private $dbconn;

    function __construct($db)
    {
        $this->dbconn = $db;
    }

    function returnAllFormDetails()
    {
        $sql = 'SELECT ID, ID, name, title, description FROM project.forms';
        $stmt = $this->dbconn->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_UNIQUE|\PDO::FETCH_ASSOC);

        return $results;
    }

    function submitForm($params) {
        print_r($params);

        $originalSQL = "INSERT INTO project.testinput ( one, two, three ) VALUES (@@dropdown_1, @@dropdown_2, @@textbox_3')";

        $newSQL = str_replace()
    }
}