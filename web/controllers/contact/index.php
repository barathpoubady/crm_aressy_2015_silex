<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../src/app.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->match('/contact/list', function (Request $request) use ($app) {  
    $start = 0;
    $vars = $request->query->all();
    $qsStart = (int)$vars["start"];
    $search = $vars["search"];
    $order = $vars["order"];
    $columns = $vars["columns"];
    $qsLength = (int)$vars["length"];    
    
    if($qsStart) {
        $start = $qsStart;
    }    
	
    $index = $start;   
    $rowsPerPage = $qsLength;
       
    $rows = array();
    
    $searchValue = $search['value'];
    $orderValue = $order[0];
    
    $orderClause = "";
    if($orderValue) {
        $orderClause = " ORDER BY ". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'id', 
		'email', 
		'nom', 
		'prenom', 
		'titre', 
		'societe', 
		'telephone', 
		'ville', 
		'cp', 
		'adresse_1', 
		'adresse_2', 
		'vendeur', 
		'contact_model_definition', 
		'profile', 
		'engagement', 

    );
    
    $whereClause = "";
    
    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE ";
        }
        
        if ($i > 0) {

              $whereClause =  $whereClause . " OR";

            }

          $whereClause =  $whereClause . " " . $col . " LIKE '%". $searchValue ."%'";

        $i = $i + 1;
    }
    

    
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `contact`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `contact`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    
    $rows[0]["etat2"]= $find_sql;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

		$rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];
               
        }

        $score = $row_sql["profile"];
        $score .= $row_sql["engagement"];
        
        
        if($score == "A1" || $score == "A2" || $score == "A3" || $score == "B1" || $score == "B2" || $score == "C1"){
            
            $rows[$row_key]["etat"] = "HOT";
            
        }else{
            
            if($score == "A4" || $score == "B3" || $score == "B4" || $score == "C2" || $score == "C3" || $score == "C4" || $score == "D1" || $score == "D2"){
                
                $rows[$row_key]["etat"] = "WARM";
                
            }else{
                
                if($score == "D3" || $score == "D4"){
                    
                    $rows[$row_key]["etat"] = "COLD";
                    
                }
                
            }

        }
        
        
        
       /* if($score == "A1" || $score == "A2" || $score == "A3" || $score == "B1" || $score == "B2" || $score == "C1"){
            
            $rows[$row_key]["etat"] = "HOT";
            
        }else{
            
            if($score == "A4" || $score == "C2" || $score == "C3" || $score == "C4" || $score == "D1" || $score == "D2"){
                
                $rows[$row_key]["etat"] = "WARM";
                
            }else{
                
                if($score == "D3" || $score == "D4"){
                    
                    $rows[$row_key]["etat"] = "COLD";
                    
                }
                
            }

        }*/
        
        
        /* LECTURE et PARCOURS De Table etat_contact - Si contacté ou pas */
        
        /*$find_sql_2 = "SELECT * FROM `etat_contact` WHERE `email` = ?";
        $row_sql_2 = $app['db']->fetchAssoc($find_sql_2, array($row_sql[$table_columns[1]]));

        if($row_sql_2){
            
            //var_dump($row_sql_2); die();
            
            $rows[$row_key]["etat"] = $row_sql_2['etat']; 
            
        }else{
            
           $rows[$row_key]["etat"] = 0;  
            
        }*/
 
    }    
    
    $queryData = new queryData();
    $queryData->start = $start;
    $queryData->recordsTotal = $recordsTotal;
    $queryData->recordsFiltered = $recordsTotal;
    $queryData->data = $rows;
    
    return new Response(json_encode($queryData), 200);
});

$app->match('/contact', function () use ($app) {
    
        $totalHot = $app['db']->executeQuery("SELECT * FROM `contact` WHERE "
                    . "'A1' = CONCAT(`profile`, `engagement`)"
                    . " OR 'A2' = CONCAT(`profile`, `engagement`)"
                    . " OR 'A3' = CONCAT(`profile`, `engagement`)"
                    . " OR 'B1' = CONCAT(`profile`, `engagement`)"
                    . " OR 'B2' = CONCAT(`profile`, `engagement`) "
                    . " OR 'C1' = CONCAT(`profile`, `engagement`) ")->rowCount();
    
        $totalWarm = $app['db']->executeQuery("SELECT * FROM `contact` WHERE "
                    . "'A4' = CONCAT(`profile`, `engagement`)"
                    . " OR 'B3' = CONCAT(`profile`, `engagement`)"
                    . " OR 'B4' = CONCAT(`profile`, `engagement`)"
                    . " OR 'C2' = CONCAT(`profile`, `engagement`)"
                    . " OR 'C3' = CONCAT(`profile`, `engagement`)"
                    . " OR 'C4' = CONCAT(`profile`, `engagement`)"
                    . " OR 'D1' = CONCAT(`profile`, `engagement`)"
                    . " OR 'D2' = CONCAT(`profile`, `engagement`)")->rowCount();
        
        $totalCold = $app['db']->executeQuery("SELECT * FROM `contact` WHERE "
                    . "'D3' = CONCAT(`profile`, `engagement`)"
                    . " OR 'D4' = CONCAT(`profile`, `engagement`)")->rowCount();
    
        $totalAutre = $app['db']->executeQuery("SELECT * FROM `contact` WHERE "
                    . "'A1' != CONCAT(`profile`, `engagement`)"
                    . " AND 'A2' != CONCAT(`profile`, `engagement`)"
                    . " AND 'A3' != CONCAT(`profile`, `engagement`)"
                    . " AND 'A4' != CONCAT(`profile`, `engagement`)"
                    . " AND 'B1' != CONCAT(`profile`, `engagement`)"
                    . " AND 'B2' != CONCAT(`profile`, `engagement`)"
                    . " AND 'B3' != CONCAT(`profile`, `engagement`)"
                    . " AND 'B4' != CONCAT(`profile`, `engagement`)"
                    . " AND 'C1' != CONCAT(`profile`, `engagement`)"
                    . " AND 'C2' != CONCAT(`profile`, `engagement`)"
                    . " AND 'C3' != CONCAT(`profile`, `engagement`)"
                    . " AND 'C4' != CONCAT(`profile`, `engagement`)"
                    . " AND 'D1' != CONCAT(`profile`, `engagement`)"
                    . " AND 'D2' != CONCAT(`profile`, `engagement`)"
                    . " AND 'D3' != CONCAT(`profile`, `engagement`)"
                    . " AND 'D4' != CONCAT(`profile`, `engagement`)")->rowCount();
        
        $totalEloqua = $app['db']->executeQuery("SELECT * FROM `contact` WHERE `contact_model_definition` = 'Webcast-eloqua-scoring'")->rowCount();
        
        $totalContactAutre = $app['db']->executeQuery("SELECT * FROM `contact` WHERE `contact_model_definition` != 'Webcast-eloqua-scoring'")->rowCount();
        
	$table_columns = array(
		'id', 
		'email', 
                'telephone',
		'nom', 
		'prenom', 
		'titre', 
		'societe', 
		'ville', 
		'cp', 
		'adresse_1', 
		'adresse_2', 
		'vendeur', 
		'contact_model_definition', 
		'profile', 
		'engagement', 

    );

    $primary_key = "id";	

    return $app['twig']->render('contact/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key,
        "nb_hot" => $totalHot,
        "nb_warm" => $totalWarm,
        "nb_cold" => $totalCold,
        "nb_autre" => $totalAutre,
        "nb_contact_eloqua" => $totalEloqua,
        "total_contact_autre" => $totalContactAutre
    ));
        
})
->bind('contact_list');

/** AJAX FILTRE HOT WARM COLD **/
$app->match('/contact/list/{filtre}', function ($filtre, Request $request) use ($app) {  
    $start = 0;
    $vars = $request->query->all();
    $qsStart = (int)$vars["start"];
    $search = $vars["search"];
    $order = $vars["order"];
    $columns = $vars["columns"];
    $qsLength = (int)$vars["length"];    
    
    if($qsStart) {
        $start = $qsStart;
    }    
	
    $index = $start;   
    $rowsPerPage = $qsLength;
       
    $rows = array();
    
    $searchValue = $search['value'];
    $orderValue = $order[0];
    
    $orderClause = "";
    if($orderValue) {
        $orderClause = " ORDER BY ". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'id', 
		'email', 
		'nom', 
		'prenom', 
		'titre', 
		'societe', 
		'telephone', 
		'ville', 
		'cp', 
		'adresse_1', 
		'adresse_2', 
		'vendeur', 
		'contact_model_definition', 
		'profile', 
		'engagement', 

    );
    
    $whereClause = "";
    
    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE (";
        }
        
        if ($i > 0) {
            
            if($col == 'profile'){
                
                $whereClause =  $whereClause . ") AND";
            }else{
                
                if($col == 'engagement'){
                    
                   //On fait rien
                }else{
                    
                    $whereClause =  $whereClause . " OR";
                }

            }
             
        }
        
         if($col == 'profile'){

             //On fait rien
         }else{
             
              if($col == 'engagement'){

              }else{
                  
                  $whereClause =  $whereClause . " " . $col . " LIKE '%". $searchValue ."%'";
              }
         }
        
        
        
        $i = $i + 1;
    }
    
    if($filtre == "hot"){
        $whereClause =  $whereClause . " ( "
                    . "'A1' = CONCAT(`profile`, `engagement`)"
                    . " OR 'A2' = CONCAT(`profile`, `engagement`)"
                    . " OR 'A3' = CONCAT(`profile`, `engagement`)"
                    . " OR 'B1' = CONCAT(`profile`, `engagement`)"
                    . " OR 'B2' = CONCAT(`profile`, `engagement`) "
                    . " OR 'C1' = CONCAT(`profile`, `engagement`)) ";
    
    }else{
        if($filtre == "warm"){
            
            $whereClause =  $whereClause . " ( "
                    . "'A4' = CONCAT(`profile`, `engagement`)"
                    . " OR 'B3' = CONCAT(`profile`, `engagement`)"
                    . " OR 'B4' = CONCAT(`profile`, `engagement`)"
                    . " OR 'C2' = CONCAT(`profile`, `engagement`)"
                    . " OR 'C3' = CONCAT(`profile`, `engagement`)"
                    . " OR 'C4' = CONCAT(`profile`, `engagement`)"
                    . " OR 'D1' = CONCAT(`profile`, `engagement`) "
                    . " OR 'D2' = CONCAT(`profile`, `engagement`)) ";
    
            
        }else{
             if($filtre == "cold"){
                 
                 $whereClause =  $whereClause . " ( "
                    . "'D3' = CONCAT(`profile`, `engagement`)"
                    . " OR 'D4' = CONCAT(`profile`, `engagement`)) ";
                 
             }else{
                 
                 if($filtre == "autre"){
                 
                    $whereClause =  $whereClause . " ( "
                        . "'A1' != CONCAT(`profile`, `engagement`)"
                        . " AND 'A2' != CONCAT(`profile`, `engagement`)"
                        . " AND 'A3' != CONCAT(`profile`, `engagement`)"
                        . " AND 'A4' != CONCAT(`profile`, `engagement`)"
                        . " AND 'B1' != CONCAT(`profile`, `engagement`)"
                        . " AND 'B2' != CONCAT(`profile`, `engagement`)"
                        . " AND 'B3' != CONCAT(`profile`, `engagement`)"
                        . " AND 'B4' != CONCAT(`profile`, `engagement`)"
                        . " AND 'C1' != CONCAT(`profile`, `engagement`)"
                        . " AND 'C2' != CONCAT(`profile`, `engagement`)"
                        . " AND 'C3' != CONCAT(`profile`, `engagement`)"
                        . " AND 'C4' != CONCAT(`profile`, `engagement`)"
                        . " AND 'D1' != CONCAT(`profile`, `engagement`)"
                        . " AND 'D2' != CONCAT(`profile`, `engagement`)"
                        . " AND 'D3' != CONCAT(`profile`, `engagement`)"
                        . " AND 'D4' != CONCAT(`profile`, `engagement`)) ";

                 }else{
                       
                     if($filtre == "eloqua"){
                         
                         $whereClause =  $whereClause . " ( `contact_model_definition` = 'Webcast-eloqua-scoring') ";
                         
                     }else{
                         
                         if($filtre == "contactautres"){
                         
                                $whereClause =  $whereClause . " ( `contact_model_definition` != 'Webcast-eloqua-scoring') ";
                         
                         }else{
                             
                             $whereClause =  $whereClause . " ( `profile` != '-1' AND `engagement` != '-1' ) ";
                             
                         }
                         
                     }

                 }

             }

        }

    }
    
    
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `contact`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `contact`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    //var_dump($find_sql);die();
    $rows[0]["etat2"]= $find_sql;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

		$rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];
               
        }

        $score = $row_sql["profile"];
        $score .= $row_sql["engagement"];
        
        
        if($score == "A1" || $score == "A2" || $score == "A3" || $score == "B1" || $score == "B2" || $score == "C1"){
            
            $rows[$row_key]["etat"] = "HOT";
            
        }else{
            
            if($score == "A4" || $score == "B3" || $score == "B4" || $score == "C2" || $score == "C3" || $score == "C4" || $score == "D1" || $score == "D2"){
                
                $rows[$row_key]["etat"] = "WARM";
                
            }else{
                
                if($score == "D3" || $score == "D4"){
                    
                    $rows[$row_key]["etat"] = "COLD";
                    
                }
                
            }

        }
    }    
    
    $queryData = new queryData();
    $queryData->start = $start;
    $queryData->recordsTotal = $recordsTotal;
    $queryData->recordsFiltered = $recordsTotal;
    $queryData->data = $rows;
    
    return new Response(json_encode($queryData), 200);
});

$app->match('/contact/filtre/{filtre}', function ($filtre) use ($app) {
    
        $totalHot = $app['db']->executeQuery("SELECT * FROM `contact` WHERE "
                    . "'A1' = CONCAT(`profile`, `engagement`)"
                    . " OR 'A2' = CONCAT(`profile`, `engagement`)"
                    . " OR 'A3' = CONCAT(`profile`, `engagement`)"
                    . " OR 'B1' = CONCAT(`profile`, `engagement`)"
                    . " OR 'B2' = CONCAT(`profile`, `engagement`) "
                    . " OR 'C1' = CONCAT(`profile`, `engagement`) ")->rowCount();
    
        $totalWarm = $app['db']->executeQuery("SELECT * FROM `contact` WHERE "
                    . "'A4' = CONCAT(`profile`, `engagement`)"
                    . " OR 'B3' = CONCAT(`profile`, `engagement`)"
                    . " OR 'B4' = CONCAT(`profile`, `engagement`)"
                    . " OR 'C2' = CONCAT(`profile`, `engagement`)"
                    . " OR 'C3' = CONCAT(`profile`, `engagement`)"
                    . " OR 'C4' = CONCAT(`profile`, `engagement`)"
                    . " OR 'D1' = CONCAT(`profile`, `engagement`)"
                    . " OR 'D2' = CONCAT(`profile`, `engagement`)")->rowCount();
        
        $totalCold = $app['db']->executeQuery("SELECT * FROM `contact` WHERE "
                    . "'D3' = CONCAT(`profile`, `engagement`)"
                    . " OR 'D4' = CONCAT(`profile`, `engagement`)")->rowCount();
        
        $totalAutre = $app['db']->executeQuery("SELECT * FROM `contact` WHERE "
                    . "'A1' != CONCAT(`profile`, `engagement`)"
                    . " AND 'A2' != CONCAT(`profile`, `engagement`)"
                    . " AND 'A3' != CONCAT(`profile`, `engagement`)"
                    . " AND 'A4' != CONCAT(`profile`, `engagement`)"
                    . " AND 'B1' != CONCAT(`profile`, `engagement`)"
                    . " AND 'B2' != CONCAT(`profile`, `engagement`)"
                    . " AND 'B3' != CONCAT(`profile`, `engagement`)"
                    . " AND 'B4' != CONCAT(`profile`, `engagement`)"
                    . " AND 'C1' != CONCAT(`profile`, `engagement`)"
                    . " AND 'C2' != CONCAT(`profile`, `engagement`)"
                    . " AND 'C3' != CONCAT(`profile`, `engagement`)"
                    . " AND 'C4' != CONCAT(`profile`, `engagement`)"
                    . " AND 'D1' != CONCAT(`profile`, `engagement`)"
                    . " AND 'D2' != CONCAT(`profile`, `engagement`)"
                    . " AND 'D3' != CONCAT(`profile`, `engagement`)"
                    . " AND 'D4' != CONCAT(`profile`, `engagement`)")->rowCount();
    
        $totalEloqua = $app['db']->executeQuery("SELECT * FROM `contact` WHERE `contact_model_definition` = 'Webcast-eloqua-scoring'")->rowCount();
        
        $totalContactAutre = $app['db']->executeQuery("SELECT * FROM `contact` WHERE `contact_model_definition` != 'Webcast-eloqua-scoring'")->rowCount();
        
	$table_columns = array(
		'id', 
		'email', 
		'nom', 
		'prenom', 
		'titre', 
		'societe', 
		'telephone', 
		'ville', 
		'cp', 
		'adresse_1', 
		'adresse_2', 
		'vendeur', 
		'contact_model_definition', 
		'profile', 
		'engagement', 

    );

    $primary_key = "id";	

    return $app['twig']->render('contact/list_filtre.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key,
        "id_filtre" => $filtre,
        "nb_hot" => $totalHot,
        "nb_warm" => $totalWarm,
        "nb_cold" => $totalCold,
        "nb_autre" => $totalAutre,
        "nb_contact_eloqua" => $totalEloqua,
        "total_contact_autre" => $totalContactAutre
        
    ));
        
})
->bind('contact_list_2');


/*** AFFICHAGE DES CONTACTS LIÉE A L'ADMIN COURANT ***/
$app->match('/contact/mescontact/list', function (Request $request) use ($app) {  
    $start = 0;
    $vars = $request->query->all();
    $qsStart = (int)$vars["start"];
    $search = $vars["search"];
    $order = $vars["order"];
    $columns = $vars["columns"];
    $qsLength = (int)$vars["length"];    
    
    if($qsStart) {
        $start = $qsStart;
    }    
	
    $index = $start;   
    $rowsPerPage = $qsLength;
       
    $rows = array();
    
    $searchValue = $search['value'];
    $orderValue = $order[0];
    
    $orderClause = "";
    if($orderValue) {
        $orderClause = " ORDER BY contact.". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'id', 
		'email', 
		'nom', 
		'prenom', 
		'titre', 
		'societe', 
		'telephone', 
		'ville', 
		'cp', 
		'adresse_1', 
		'adresse_2', 
		'vendeur', 
		'contact_model_definition', 
		'profile', 
		'engagement', 

    );
    
    $whereClause = "";
    
    $i = 0;

    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE";
        }
        
        if ($i > 0) {

              $whereClause =  $whereClause . " OR";

            }

          $whereClause =  $whereClause . " contact." . $col . " LIKE '%". $searchValue ."%'";

        $i = $i + 1;
    }
    
    $current_user = $app['userInfo_2'];
    
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `contact` INNER JOIN `admin_contact` ON contact.id=admin_contact.id_contact AND admin_contact.id_admin='".$current_user['id']."'". $whereClause . $orderClause)->rowCount();

    $find_sql = "SELECT * FROM `contact` INNER JOIN `admin_contact` ON contact.id=admin_contact.id_contact AND admin_contact.id_admin='".$current_user['id']."'". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    
    //var_dump($find_sql); die();
    
    $rows[0]["etat2"]= $find_sql;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

            $rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];
               
        }
        
         $rows[$row_key]["id_contact"] = $row_sql["id_contact"];

        $score = $row_sql["profile"];
        $score .= $row_sql["engagement"];
        
        
        if($score == "A1" || $score == "A2" || $score == "A3" || $score == "B1" || $score == "B2" || $score == "C1"){
            
            $rows[$row_key]["etat"] = "HOT";
            
        }else{
            
            if($score == "A4" || $score == "B3" || $score == "B4" || $score == "C2" || $score == "C3" || $score == "C4" || $score == "D1" || $score == "D2"){
                
                $rows[$row_key]["etat"] = "WARM";
                
            }else{
                
                if($score == "D3" || $score == "D4"){
                    
                    $rows[$row_key]["etat"] = "COLD";
                    
                }
                
            }

        }  
        
    }
    
    $queryData = new queryData();
    $queryData->start = $start;
    $queryData->recordsTotal = $recordsTotal;
    $queryData->recordsFiltered = $recordsTotal;
    $queryData->data = $rows;
    
    return new Response(json_encode($queryData), 200);
});


$app->match('/contact/mescontact', function () use ($app) {
    
        $current_user = $app['userInfo_2'];
    
        $totalHot = $app['db']->executeQuery("SELECT * FROM `contact` INNER JOIN `admin_contact` ON contact.id=admin_contact.id_contact AND admin_contact.id_admin='".$current_user['id']
                    . "' WHERE 'A1' = CONCAT(contact.profile, contact.engagement)"
                    . " OR 'A2' = CONCAT(contact.profile, contact.engagement)"
                    . " OR 'A3' = CONCAT(contact.profile, contact.engagement)"
                    . " OR 'B1' = CONCAT(contact.profile, contact.engagement)"
                    . " OR 'B2' = CONCAT(contact.profile, contact.engagement) "
                    . " OR 'C1' = CONCAT(contact.profile, contact.engagement) ")->rowCount();
    
        $totalWarm = $app['db']->executeQuery("SELECT * FROM `contact` INNER JOIN `admin_contact` ON contact.id=admin_contact.id_contact AND admin_contact.id_admin='".$current_user['id']
                    . "' WHERE 'A4' = CONCAT(contact.profile, contact.engagement)"
                    . " OR 'B3' = CONCAT(contact.profile, contact.engagement)"
                    . " OR 'B4' = CONCAT(contact.profile, contact.engagement)"
                    . " OR 'C2' = CONCAT(contact.profile, contact.engagement)"
                    . " OR 'C3' = CONCAT(contact.profile, contact.engagement)"
                    . " OR 'C4' = CONCAT(contact.profile, contact.engagement)"
                    . " OR 'D1' = CONCAT(contact.profile, contact.engagement)"
                    . " OR 'D2' = CONCAT(contact.profile, contact.engagement)")->rowCount();
        
        $totalCold = $app['db']->executeQuery("SELECT * FROM `contact` INNER JOIN `admin_contact` ON contact.id=admin_contact.id_contact AND admin_contact.id_admin='".$current_user['id']
                    . "' WHERE 'D3' = CONCAT(contact.profile, contact.engagement)"
                    . " OR 'D4' = CONCAT(contact.profile, contact.engagement)")->rowCount();
    
        $totalAutre = $app['db']->executeQuery("SELECT * FROM `contact` INNER JOIN `admin_contact` ON contact.id=admin_contact.id_contact AND admin_contact.id_admin='".$current_user['id']
                    . "' WHERE 'A1' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'A2' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'A3' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'A4' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'B1' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'B2' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'B3' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'B4' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'C1' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'C2' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'C3' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'C4' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'D1' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'D2' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'D3' != CONCAT(contact.profile, contact.engagement)"
                    . " AND 'D4' != CONCAT(contact.profile, contact.engagement)")->rowCount();
        
        $totalEloqua = $app['db']->executeQuery("SELECT * FROM `contact` INNER JOIN `admin_contact` ON contact.id=admin_contact.id_contact AND admin_contact.id_admin='".$current_user['id']."' WHERE `contact_model_definition` = 'Webcast-eloqua-scoring'")->rowCount();
        
        $totalContactAutre = $app['db']->executeQuery("SELECT * FROM `contact` INNER JOIN `admin_contact` ON contact.id=admin_contact.id_contact AND admin_contact.id_admin='".$current_user['id']."' WHERE `contact_model_definition` != 'Webcast-eloqua-scoring'")->rowCount();
        
	$table_columns = array(
		'id', 
		'email', 
        'telephone',
		'nom', 
		'prenom', 
		'titre', 
		'societe', 
		'ville', 
		'cp', 
		'adresse_1', 
		'adresse_2', 
		'vendeur', 
		'contact_model_definition', 
		'profile', 
		'engagement', 

    );

    $primary_key = "id_contact";	

    return $app['twig']->render('contact/mescontact_list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key,
        "nb_hot" => $totalHot,
        "nb_warm" => $totalWarm,
        "nb_cold" => $totalCold,
        "nb_autre" => $totalAutre,
        "nb_contact_eloqua" => $totalEloqua,
        "total_contact_autre" => $totalContactAutre
    ));
        
})
->bind('contact_mescontact_list');

/*** DISSOCIER UN CONTACT A l'ADMIN COURANT ***/

$app->match('/contact/dissocier/{id_contact}', function ($id_contact) use ($app) {
    
    $current_user = $app['userInfo_2'];
    
    //On recupere le contact
    $find_sql = "SELECT * FROM `admin_contact` WHERE `id_admin` = ? AND `id_contact` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($current_user['id'], $id_contact));
    
    // Si le contact est déjà associé a l'admin
    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'warning',
            array(
                'message' => 'WARNING Contact pas associer !',
            )
        );        
        return $app->redirect($app['url_generator']->generate('voir_contact', array("id" => $id_contact)));
        
    }

    $delete_query = "DELETE FROM `admin_contact` WHERE `id_admin` = ? AND `id_contact` = ?";
    $app['db']->executeUpdate($delete_query, array($current_user['id'], $id_contact));

    $app['session']->getFlashBag()->add(
        'success',
        array(
            'message' => 'Contact dissocié de '.$current_user['prenom'].' !',
        )
    );

    return $app->redirect($app['url_generator']->generate('voir_contact', array("id" => $id_contact)));

})->bind('dissocier_contact');

/*** AJOUT DU CONTACT A l'ADMIN COURANT ***/

$app->match('/contact/ajouter/{id_contact}', function ($id_contact) use ($app) {
    
    $current_user = $app['userInfo_2'];
    
    //On recupere le contact
    $find_sql = "SELECT * FROM `admin_contact` WHERE `id_admin` = ? AND `id_contact` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($current_user['id'], $id_contact));
    
    // Si le contact est déjà associé a l'admin
    if($row_sql){
        $app['session']->getFlashBag()->add(
            'warning',
            array(
                'message' => 'WARNING Contact déjà ajouté !',
            )
        );        
        return $app->redirect($app['url_generator']->generate('voir_contact', array("id" => $id_contact)));
        
    }
    
    
    $update_query = "INSERT INTO `admin_contact` (`id_admin`, `id_contact`) VALUES (?, ?)";
    $app['db']->executeUpdate($update_query, array($current_user['id'], $id_contact));            


    $app['session']->getFlashBag()->add(
        'success',
        array(
            'message' => 'Contact ajouté à '.$current_user['prenom'].' !',
        )
    );

    return $app->redirect($app['url_generator']->generate('voir_contact', array("id" => $id_contact)));
    
    /*return $app['twig']->render('contact/ajouter_contact_admin.html.twig', array(
    	"current_user" => $current_user,
        "primary_key" => $id_contact,

    ));*/
    
})->bind('ajouter_contact');

/*** AFFICHE LE CONTACT EN DETAIL ***/

$app->match('/contact/voir/{id}', function ($id) use ($app) {
    
    //On recupere le contact
    $find_sql = "SELECT * FROM `contact` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'ERREUR Impossible de trouver le contact !',
            )
        );        
        return $app->redirect($app['url_generator']->generate('contact_list'));
    }
    
    //Variables de data
    $contact = $row_sql;
    $primary_key = $id;
    $testContact = 0;
    
    //recupere l'admin courant
    $current_user = $app['userInfo_2'];
    
    $find_sql_2 = "SELECT * FROM `admin_contact` WHERE `id_admin` = ? AND `id_contact` = ?";
    $row_sql_2 = $app['db']->fetchAssoc($find_sql_2, array($current_user['id'], $id));
    
    // Test si déjà associé ou pas
    if($row_sql_2){
        $testContact = 1;
    }
    
    //Info Appel | Email | RDV
    $nbEmail = $app['db']->executeQuery("SELECT * FROM `email` WHERE `id_admin` ='".$current_user['id']."'  AND `id_contact` = '".$id."'")->rowCount();
    $find_sql_email = "SELECT * FROM `email` WHERE `id_admin` = ? AND `id_contact` = ?";
    $row_sql_email = $app['db']->fetchAssoc($find_sql_email, array($current_user['id'], $id));

    return $app['twig']->render('contact/voir_contact.html.twig', array(
    	"contact" => $contact,
        "primary_key" => $primary_key,
        "test_contact" => $testContact,
        "nb_email" => $nbEmail,
        "liste_email" => $row_sql_email,
        
    ));
    
})->bind('voir_contact');

/*** AFFICHE LES DETAILS DES EMAILS ENVOYE ENTRE L'ADMIN et LE CONTACT  ***/

/*** C.R.U.D ***/

$app->match('/contact/create', function () use ($app) {
    
    $initial_data = array(
		'email' => '', 
		'nom' => '', 
		'prenom' => '', 
		'titre' => '', 
		'societe' => '', 
		'telephone' => '', 
		'ville' => '', 
		'cp' => '', 
		'adresse_1' => '', 
		'adresse_2' => '', 
		'vendeur' => '', 
		'contact_model_definition' => '', 
		'profile' => '', 
		'engagement' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$form = $form->add('email', 'text', array('required' => true));
	$form = $form->add('nom', 'text', array('required' => true));
	$form = $form->add('prenom', 'text', array('required' => true));
	$form = $form->add('titre', 'text', array('required' => true));
	$form = $form->add('societe', 'text', array('required' => true));
	$form = $form->add('telephone', 'text', array('required' => true));
	$form = $form->add('ville', 'text', array('required' => true));
	$form = $form->add('cp', 'text', array('required' => true));
	$form = $form->add('adresse_1', 'text', array('required' => true));
	$form = $form->add('adresse_2', 'text', array('required' => true));
	$form = $form->add('vendeur', 'text', array('required' => true));
	$form = $form->add('contact_model_definition', 'text', array('required' => true));
	$form = $form->add('profile', 'text', array('required' => true));
	$form = $form->add('engagement', 'text', array('required' => true));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
            
             $find_sql_2 = "SELECT * FROM `contact` WHERE `email` = ?";
             $row_sql_2 = $app['db']->fetchAssoc($find_sql_2, array($data['email']));
            
            if($row_sql_2){
            
                $app['session']->getFlashBag()->add(
                    'danger',
                    array(
                        'message' => 'Contact Déjà existant !',
                    )
                );        
                return $app->redirect($app['url_generator']->generate('contact_list'));
            }
            
            $update_query = "INSERT INTO `contact` (`email`, `nom`, `prenom`, `titre`, `societe`, `telephone`, `ville`, `cp`, `adresse_1`, `adresse_2`, `vendeur`, `contact_model_definition`, `profile`, `engagement`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['email'], $data['nom'], $data['prenom'], $data['titre'], $data['societe'], $data['telephone'], $data['ville'], $data['cp'], $data['adresse_1'], $data['adresse_2'], $data['vendeur'], $data['contact_model_definition'], $data['profile'], $data['engagement']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'contact ajouté !',
                )
            );
            return $app->redirect($app['url_generator']->generate('contact_list'));

        }
    }

    return $app['twig']->render('contact/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('contact_create');



$app->match('/contact/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `contact` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('contact_list'));
    }

    
    $initial_data = array(
		'email' => $row_sql['email'], 
		'nom' => $row_sql['nom'], 
		'prenom' => $row_sql['prenom'], 
		'titre' => $row_sql['titre'], 
		'societe' => $row_sql['societe'], 
		'telephone' => $row_sql['telephone'], 
		'ville' => $row_sql['ville'], 
		'cp' => $row_sql['cp'], 
		'adresse_1' => $row_sql['adresse_1'], 
		'adresse_2' => $row_sql['adresse_2'], 
		'vendeur' => $row_sql['vendeur'], 
		'contact_model_definition' => $row_sql['contact_model_definition'], 
		'profile' => $row_sql['profile'], 
		'engagement' => $row_sql['engagement'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('email', 'text', array('required' => true));
	$form = $form->add('nom', 'text', array('required' => true));
	$form = $form->add('prenom', 'text', array('required' => true));
	$form = $form->add('titre', 'text', array('required' => true));
	$form = $form->add('societe', 'text', array('required' => true));
	$form = $form->add('telephone', 'text', array('required' => true));
	$form = $form->add('ville', 'text', array('required' => true));
	$form = $form->add('cp', 'text', array('required' => true));
	$form = $form->add('adresse_1', 'text', array('required' => true));
	$form = $form->add('adresse_2', 'text', array('required' => true));
	$form = $form->add('vendeur', 'text', array('required' => true));
	$form = $form->add('contact_model_definition', 'text', array('required' => true));
	$form = $form->add('profile', 'text', array('required' => true));
	$form = $form->add('engagement', 'text', array('required' => true));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `contact` SET `email` = ?, `nom` = ?, `prenom` = ?, `titre` = ?, `societe` = ?, `telephone` = ?, `ville` = ?, `cp` = ?, `adresse_1` = ?, `adresse_2` = ?, `vendeur` = ?, `contact_model_definition` = ?, `profile` = ?, `engagement` = ? WHERE `id` = ?";
            $app['db']->executeUpdate($update_query, array($data['email'], $data['nom'], $data['prenom'], $data['titre'], $data['societe'], $data['telephone'], $data['ville'], $data['cp'], $data['adresse_1'], $data['adresse_2'], $data['vendeur'], $data['contact_model_definition'], $data['profile'], $data['engagement'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'contact edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('voir_contact', array("id" => $id)));

        }
    }

    return $app['twig']->render('contact/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('contact_edit');



$app->match('/contact/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `contact` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `contact` WHERE `id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'contact deleted!',
            )
        );
    }
    else{
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );  
    }

    return $app->redirect($app['url_generator']->generate('contact_list'));

})
->bind('contact_delete');

/*** ENVOIE MAIL ***/

$app->match('/contact/mail/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `contact` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'ERREUR MAIL Contact introuvable !',
            )
        );        
        return $app->redirect($app['url_generator']->generate('voir_contact', array("id" => $id)));
    }

    
    $initial_data = array(
		'email' => $row_sql['email'],
                'sujet' => '',
                'message' => '',
		/*'nom' => $row_sql['nom'], 
		'prenom' => $row_sql['prenom'], 
		'titre' => $row_sql['titre'], 
		'societe' => $row_sql['societe'], 
		'telephone' => $row_sql['telephone'], 
		'ville' => $row_sql['ville'], 
		'cp' => $row_sql['cp'], 
		'adresse_1' => $row_sql['adresse_1'], 
		'adresse_2' => $row_sql['adresse_2'], 
		'vendeur' => $row_sql['vendeur'], 
		'contact_model_definition' => $row_sql['contact_model_definition'], 
		'profile' => $row_sql['profile'], 
		'engagement' => $row_sql['engagement'], */

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('email', 'email', array('required' => true));
        $form = $form->add('sujet', 'text', array('required' => true));
        $form = $form->add('message', 'textarea', array('required' => true));
	/*$form = $form->add('nom', 'text', array('required' => true));
	$form = $form->add('prenom', 'text', array('required' => true));
	$form = $form->add('titre', 'text', array('required' => true));
	$form = $form->add('societe', 'text', array('required' => true));
	$form = $form->add('telephone', 'text', array('required' => true));
	$form = $form->add('ville', 'text', array('required' => true));
	$form = $form->add('cp', 'text', array('required' => true));
	$form = $form->add('adresse_1', 'text', array('required' => true));
	$form = $form->add('adresse_2', 'text', array('required' => true));
	$form = $form->add('vendeur', 'text', array('required' => true));
	$form = $form->add('contact_model_definition', 'text', array('required' => true));
	$form = $form->add('profile', 'text', array('required' => true));
	$form = $form->add('engagement', 'text', array('required' => true));*/


    $form = $form->getForm();

    /* SI VALIDATION ENVOIE par POST */
    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

             // echo $data['email']; die();
            
            //recupere l'admin courant
            $current_user = $app['userInfo_2'];
            
            $date = new DateTime();
             
            
            
            $insert_sql = "INSERT INTO `email` (`email_to`, `sujet`, `message`, `heure_envoie`, `id_admin`, `id_contact`) VALUES (?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($insert_sql, array($data['email'], $data['sujet'], $data['message'], $date->format('Y-m-d H:i:s'), $current_user['id'], $id));            

            
             //var_dump($current_user);die();
             
            //Envoie du mail
             $message = Swift_Message::newInstance()
             ->setSubject($data['sujet'])
             ->setFrom(array($current_user['login']))
             ->setTo(array($data['email']))
             ->setBody($data['message'], 'text/html');
             
             $app['mailer'] = new \Swift_MailTransport;
           
             $app['mailer']->send($message);

            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'Votre email a été transféré à ' .$data['email'] ,
                )
            );
            return $app->redirect($app['url_generator']->generate('voir_contact', array("id" => $id)));

        }
    }

    return $app['twig']->render('contact/mail.html.twig', array(
        "form" => $form->createView(),
        "id" => $id,
        "contact_info" => $row_sql
    ));
        
})
->bind('contact_mail');





