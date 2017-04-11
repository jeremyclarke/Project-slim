<?php

namespace App\Controllers;

class ObjectController extends Controller
{
    private $statementResults;

    function __construct($db, $twig, $mail, $rlib)
    {
        parent::__construct($db, $twig, $mail, $rlib);
    }

    function returnAllFormObjects($formID)
    {
        $sql = 'SELECT ID, ID, form_ID, type, SQL_populate_query, SQL_insert_execute_query, caption, required FROM project.objects WHERE form_ID = :formID ORDER BY -obj_order DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':formID', $formID, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_UNIQUE);

        foreach ($results as $result) {
            if (!empty($result['SQL_populate_query'])) {
                $this->getFormObjectSQLResult($result['SQL_populate_query'], $result['ID']);
            }
        }
        return $results;
    }

    function getFormObjectSQLResult($sql, $id)
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        //$this->statementResults[$id] = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
        $this->statementResults[$id] = $stmt->fetchAll(\PDO::FETCH_NUM);
    }

    function getStatementResults()
    {
        return $this->statementResults;
    }
}