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

        $results = $stmt->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);

        return $results;
    }

    function submitForm($params, $formID)
    {
        $sql = "SELECT SQL_insert_execute_query FROM project.objects WHERE form_id = ". $formID . " AND type = 'button' LIMIT 1";
        $stmt = $this->dbconn->prepare($sql);
        $stmt->execute();
        $insertSQL = $stmt->fetchColumn();

        echo $insertSQL;

        print_r($params);


        //
//        $originalSQL = "INSERT INTO project.testinput ( one, two, three ) VALUES (@@dropdown_1, @@dropdown_2, @@textbox_3)";
//
//        $sqlExplode = explode("@@", $originalSQL);
//        array_shift($sqlExplode);
//
//        print_r($sqlExplode);
//        echo '</br></br></br>';
//        // echo $originalSQL;
//
//        for ($i=0; i <= count($sqlExplode); $i++) {
//            //$newSQL = str_replace('@@' . $sqlExplode[i], $params[i], $originalSQL);
//            //echo $newSQL;
//        }

        //$newSQL = str_replace();
    }
}