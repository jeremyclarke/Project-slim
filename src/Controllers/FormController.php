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
                AND ID = (SELECT form_id from project.permissions where user_id = :userID LIMIT 1)';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("userID", $userID, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

        if (!empty($results)) {
            return $results;
        }

    }

    function submitForm($params, $id)
    {
        try {
            $sql = 'SELECT submit_statement FROM project.forms WHERE ID = :id LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("id", $id, \PDO::PARAM_INT);
            $stmt->execute();

            $sqlStmt = $stmt->fetchColumn();

        } catch (\PDOException $e) {
            return array(
                'success' => false,
                'msgTitle' => 'SQL problem',
                'msgBody' => $e->getMessage()
            );
        }

        $action = explode(' ', trim($sqlStmt))[0];
        $totalParams = substr_count($sqlStmt, "@@");

        if (strtolower($action) == 'insert' || 'update' || 'call') {

            for ($i = 0; $i < sizeof($params); $i++) {
                $sqlStmt = str_replace("@@" . array_keys($params)[$i], ":" . array_keys($params)[$i], $sqlStmt);
            }

            try {
                $stmt = $this->db->prepare($sqlStmt);

                for ($i = 0; $i < $totalParams; $i++) {

                    if (empty(array_values($params)[$i])) {
                        $null = NULL;
                        $stmt->bindParam(":" . array_keys($params)[$i], $null, \PDO::PARAM_STR);

                    } else {
                        $stmt->bindParam(":" . array_keys($params)[$i], array_values($params)[$i], \PDO::PARAM_STR);
                    }

                }

                if ($stmt->execute()) {

                    return array(
                        'success' => true,
                        'msgTitle' => 'Form submitted',
                        'msgBody' => 'Thanks! Your form has been submitted successfully.'
                    );
                }
            } catch
            (\PDOException $e) {
                return array(
                    'success' => false,
                    'msgTitle' => 'SQL problem',
                    'msgBody' => $e->getMessage()
                );
            }

        } else {
            return array(
                'success' => false,
                'msgTitle' => 'SQL action problem',
                'msgBody' => 'Please check that your SQL action command is correct and try again.'
            );
        }
    }

}