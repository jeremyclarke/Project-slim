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
        $sql = 'SELECT ID, ID, name, title, description, developer_mode FROM project.forms';
        $stmt = $this->dbconn->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);

        return $results;
    }

    function submitForm($params, $formID, $response)
    {
        $sql = "SELECT SQL_insert_execute_query FROM project.objects WHERE form_id = :formID AND type = 'button' LIMIT 1";
        $stmt = $this->dbconn->prepare($sql);
        $stmt->bindParam(':formID', $formID, \PDO::PARAM_INT);
        $stmt->execute();

        $actionSQL = $stmt->fetchColumn();

        if (substr(strtoupper($actionSQL), 0, 4) === 'INSE') {
            for ($i = 0; $i < sizeof($params); $i++) {
                $actionSQL = str_replace("@@" . array_keys($params)[$i], "?", $actionSQL);
            }

            try {
                $stmt = $this->dbconn->prepare($actionSQL);

                for ($i = 0; $i < sizeof($params); $i++) {
                    $stmt->bindParam($i + 1, array_values($params)[$i], \PDO::PARAM_STR, 1);
                }

                $stmt->execute();

            } catch (\PDOException $e) {
                return $e->getMessage();
            }

        } else {
            echo 'broke';
        }
    }
}