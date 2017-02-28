<?php
/**
 * Created by PhpStorm.
 * User: jerem
 * Date: 25/02/2017
 * Time: 16:29
 */

namespace App\Controllers;


class ObjectController
{
    private $dbconn;
    private $statementResults;

    function __construct($db)
    {
        $this->dbconn = $db;
    }

    function returnAllFormObjects($formID)
    {
        $sql = 'SELECT ID, ID, form_ID, type, SQL_populate_query, SQL_insert_execute_query, caption, required FROM project.objects WHERE form_ID = ' . $formID;
        $stmt = $this->dbconn->prepare($sql);
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
        $stmt = $this->dbconn->prepare($sql);
        $stmt->execute();
        $this->statementResults[$id] = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    function getStatementResults()
    {
        return $this->statementResults;
    }
}