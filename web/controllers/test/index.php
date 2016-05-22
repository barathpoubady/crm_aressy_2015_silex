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

use Symfony\Component\Validator\Constraints as Assert;

$app->match('/test/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
		'categorie', 
		'titre', 
		'duree', 
		'etat', 

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
        
        $whereClause =  $whereClause . " " . $col . " LIKE '%". $searchValue ."%'";
        
        $i = $i + 1;
    }
    
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `test`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `test`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

		$rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];


        }
    }    
    
    $queryData = new queryData();
    $queryData->start = $start;
    $queryData->recordsTotal = $recordsTotal;
    $queryData->recordsFiltered = $recordsTotal;
    $queryData->data = $rows;
    
    return new Symfony\Component\HttpFoundation\Response(json_encode($queryData), 200);
});

$app->match('/test', function () use ($app) {
    
	$table_columns = array(
		'id', 
		'categorie', 
		'titre', 
		'duree', 
		'etat', 

    );

    $primary_key = "id";	

    return $app['twig']->render('test/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('test_list');



$app->match('/test/create', function () use ($app) {
    
    $initial_data = array(
		'categorie' => '', 
		'titre' => '', 
		'duree' => '', 
		'etat' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$form = $form->add('categorie', 'text', array('required' => true));
	$form = $form->add('titre', 'text', array('required' => true));
	$form = $form->add('duree', 'text', array('required' => true));
	$form = $form->add('etat', 'text', array('required' => true));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "INSERT INTO `test` (`categorie`, `titre`, `duree`, `etat`) VALUES (?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['categorie'], $data['titre'], $data['duree'], $data['etat']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'test created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('test_list'));

        }
    }

    return $app['twig']->render('test/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('test_create');



$app->match('/test/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `test` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('test_list'));
    }

    
    $initial_data = array(
		'categorie' => $row_sql['categorie'], 
		'titre' => $row_sql['titre'], 
		'duree' => $row_sql['duree'], 
		'etat' => $row_sql['etat'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('categorie', 'text', array('required' => true));
	$form = $form->add('titre', 'text', array('required' => true));
	$form = $form->add('duree', 'text', array('required' => true));
	$form = $form->add('etat', 'text', array('required' => true));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `test` SET `categorie` = ?, `titre` = ?, `duree` = ?, `etat` = ? WHERE `id` = ?";
            $app['db']->executeUpdate($update_query, array($data['categorie'], $data['titre'], $data['duree'], $data['etat'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'test edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('test_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('test/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('test_edit');



$app->match('/test/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `test` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `test` WHERE `id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'test deleted!',
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

    return $app->redirect($app['url_generator']->generate('test_list'));

})
->bind('test_delete');






