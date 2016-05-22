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

$app->match('/user/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
		'nom', 
		'prenom', 
		'login', 
		'password', 
		'role', 
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
    
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `user`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `user`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
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

$app->match('/user', function () use ($app) {
    
	$table_columns = array(
		'id', 
		'nom', 
		'prenom', 
		'login', 
		'password', 
		'role', 
		'etat', 

    );

    $primary_key = "id";	

    return $app['twig']->render('user/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('user_list');



$app->match('/user/create', function () use ($app) {
    
    $initial_data = array(
		'nom' => '', 
		'prenom' => '', 
		'login' => '', 
		'password' => '', 
		'role' => '', 
		'etat' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$form = $form->add('nom', 'text', array('required' => true));
	$form = $form->add('prenom', 'text', array('required' => true));
	$form = $form->add('login', 'text', array('required' => true));
	$form = $form->add('password', 'text', array('required' => true));
	$form = $form->add('role', 'text', array('required' => true));
	$form = $form->add('etat', 'text', array('required' => true));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
            
          /*** CODE PERSO ## TEST SI USER DEJA EXISTANT ***/
            
            $find_sql = "SELECT * FROM `user` WHERE login = '".$data['login']."'";
            $rows_sql = $app['db']->fetchAll($find_sql, array());
            
            
            if($rows_sql){
                $app['session']->getFlashBag()->add(
                'warning',
                    array(
                        'message' => 'Utilisateur déjà existant !',
                    )
                );
                return $app->redirect($app['url_generator']->generate('user_create'));
                
            }
          /*** CODE PERSO FIN ***/
            
            $pwd = $app['security.encoder.digest']->encodePassword($data['password'], '');
            
            $update_query = "INSERT INTO `user` (`nom`, `prenom`, `login`, `password`, `role`, `etat`) VALUES (?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['nom'], $data['prenom'], $data['login'], $pwd, $data['role'], $data['etat']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'user created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('user_list'));

        }
    }

    return $app['twig']->render('user/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('user_create');



$app->match('/user/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `user` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('user_list'));
    }

    
    $initial_data = array(
		'nom' => $row_sql['nom'], 
		'prenom' => $row_sql['prenom'], 
		'login' => $row_sql['login'], 
		'password' => $row_sql['password'], 
		'role' => $row_sql['role'], 
		'etat' => $row_sql['etat'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('nom', 'text', array('required' => true));
	$form = $form->add('prenom', 'text', array('required' => true));
	$form = $form->add('login', 'text', array('required' => true));
	$form = $form->add('password', 'text', array('required' => true));
	$form = $form->add('role', 'text', array('required' => true));
	$form = $form->add('etat', 'text', array('required' => true));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
            
            /*** CODE PERSO ## TEST SI USER DEJA EXISTANT ***/
            
            $find_sql = "SELECT * FROM `user` WHERE login = '".$data['login']."' AND id != '".$id."'";
            $rows_sql = $app['db']->fetchAll($find_sql, array());
            
            if($rows_sql){
                $app['session']->getFlashBag()->add(
                'warning',
                    array(
                        'message' => 'Le login existe déjà !',
                    )
                );
                return $app->redirect($app['url_generator']->generate('user_edit', array("id" => $id)));
                
            }
            /*** CODE PERSO ***/

            $update_query = "UPDATE `user` SET `nom` = ?, `prenom` = ?, `login` = ?, `password` = ?, `role` = ?, `etat` = ? WHERE `id` = ?";
            $app['db']->executeUpdate($update_query, array($data['nom'], $data['prenom'], $data['login'], $data['password'], $data['role'], $data['etat'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'user edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('user_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('user/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('user_edit');



$app->match('/user/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `user` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `user` WHERE `id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'user deleted!',
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

    return $app->redirect($app['url_generator']->generate('user_list'));

})
->bind('user_delete');






