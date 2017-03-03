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

    function submitForm($params, $formID, $response)
    {
        $sql = "SELECT SQL_insert_execute_query FROM project.objects WHERE form_id = " . $formID . " AND type = 'button' LIMIT 1";
        $stmt = $this->dbconn->prepare($sql);
        $stmt->execute();
        $actionSQL = $stmt->fetchColumn();

        if (substr(strtoupper($actionSQL), 0, 4) === 'INSE') {
            for ($i = 0; $i < sizeof($params); $i++) {
                $actionSQL = str_replace("@@" . array_keys($params)[$i], '\'' . array_values($params)[$i] . '\'', $actionSQL);
            }

            try {
                $stmt = $this->dbconn->prepare($actionSQL);
                $stmt->execute();
               // return $stmt->getSQLState();
            } catch (\PDOException $e) {
                return $e->getMessage();
                //return setStatus(400);
            }

        } else {
            echo 'broke';
        }


       // return 'yay';
        //return $response->withRedirect($router->pathFor('home'));
    }
}