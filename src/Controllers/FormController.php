<?php

namespace App\Controllers;

class FormController extends Controller
{
    function __construct($db, $twig, $mail, $rlib)
    {
        parent::__construct($db, $twig, $mail, $rlib);
    }


    function getForms()
    {
        $sql = 'SELECT * FROM project.forms';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $results = $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    function getSingleForm($formID)
    {
        $sql = 'SELECT * FROM project.forms WHERE ID = :ID';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("ID", $formID, \PDO::PARAM_INT);
        $stmt->execute();

        return $results = $stmt->fetch(\PDO::FETCH_OBJ);
    }

    function getPrivateForms($userID)
    {
        $sql = 'SELECT * FROM project.forms 
                WHERE private = 1 
                AND ID IN (SELECT form_id from project.permissions where user_id = :userID)';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("userID", $userID, \PDO::PARAM_INT);
        $stmt->execute();

        return $results = $stmt->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
    }



//    function returnPublicFormDetails()
//    {
//        $sql = 'SELECT ID, ID, name, title, description, developer_mode, private FROM project.forms WHERE private = 0';
//
//        $stmt = $this->db->prepare($sql);
//        $stmt->execute();
//
//        $results = $stmt->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
//
//        return $results;
//    }


//    function checkIfFormPublic($formID)
//    {
//        try { //first check if form is private or not, if not, allow it to be viewed
//            $stmt = $this->db->prepare("SELECT private FROM project.forms WHERE ID = :formID");
//            $stmt->bindParam("formID", $formID, \PDO::PARAM_INT);
//
//            $stmt->execute();
//            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
//
//            if ($row['private'] == 0) {
//                return true;
//            } else {
//                return false;
//            }
//
//        } catch (\PDOException $e) {
//            die($e->getMessage());
//        }
//    }


//    function returnAllFormDetailsPrivate($userID)
//    {
//        $sql = 'SELECT ID, ID, name, title, description, developer_mode, private FROM project.forms WHERE private = 1 AND ID IN (SELECT form_id from project.permissions where user_id = :userID)';
//        $stmt = $this->db->prepare($sql);
//        $stmt->bindParam("userID", $userID, \PDO::PARAM_INT);
//
//        $stmt->execute();
//
//        $results = $stmt->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
//
//        return $results;
//    }

    function submitForm($params, $sqlStmt)
    {
        $action = explode(' ',trim($sqlStmt))[0];

        if (strtolower($action) == 'insert') {
            for ($i = 0; $i < sizeof($params); $i++) {
                $sqlStmt = str_replace("@@" . array_keys($params)[$i], "?", $sqlStmt);
            }

            try {
                $stmt = $this->db->prepare($sqlStmt);

                for ($i = 0; $i < sizeof($params); $i++) {
                    $stmt->bindParam($i + 1, array_values($params)[$i], \PDO::PARAM_STR, 1);
                }

                $stmt->execute();

                return array('success' => true,
                    'msgTitle' => 'Form submitted',
                    'msgBody' => 'Thanks! Your form has been submitted successfully.'
                );

            } catch (\PDOException $e) {
                return array('success' => false,
                    'msgTitle' => 'SQL problem',
                    'msgBody' => $e->getMessage()
                );
            }

        } else {
            echo 'broke';
        }
    }
}