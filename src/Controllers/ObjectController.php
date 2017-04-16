<?php

namespace App\Controllers;

class ObjectController extends Controller
{
    private $statementResults;

    function __construct($db, $twig, $mail, $rlib)
    {
        parent::__construct($db, $twig, $mail, $rlib);
    }

    function getFormObjects($formID)
    {
//        $sql = 'SELECT ID, ID, form_ID, type, SQL_populate_query, caption, required, input_length, regex, validation_error FROM project.objects WHERE form_ID = :formID ORDER BY -obj_order DESC';
        $sql = 'SELECT * FROM project.objects WHERE form_ID = :formID ORDER BY -obj_order DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':formID', $formID, \PDO::PARAM_INT);
        $stmt->execute();
        $objects = $stmt->fetchAll(\PDO::FETCH_OBJ);

        foreach ($objects as $object) {
            if (!empty($object->SQL_populate_query)) {
                $this->getFormObjectSQLResult($object->SQL_populate_query, $object->ID);
            }
        }
        return $objects;
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