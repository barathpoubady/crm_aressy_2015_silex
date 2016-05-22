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
//ini_set('display_errors', 1);

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../src/app.php';


require_once __DIR__.'/contact/index.php';
require_once __DIR__.'/test/index.php';
require_once __DIR__.'/user/index.php';


$app->match('/propos', function () use ($app) {

    return $app['twig']->render('propos.html.twig', array(
        
    ));
        
})
->bind('propos');


$app->match('/dash', function () use ($app) {

     $find_sql = "SELECT COUNT(*) as nb FROM `user`";
     $rows_sql = $app['db']->fetchAll($find_sql, array());
     
     $nbUser = $rows_sql[0]['nb'];
     
     $find_sql = "SELECT COUNT(*) as nb FROM `contact`";
     $rows_sql = $app['db']->fetchAll($find_sql, array());
     
     $nbContact = $rows_sql[0]['nb'];
     
     $find_sql = "SELECT COUNT(*) as nb FROM `contact` WHERE `societe` = 'aressy';";
     $rows_sql = $app['db']->fetchAll($find_sql, array());
     
     $nbAressy = $rows_sql[0]['nb'];
     
    return $app['twig']->render('ag_dashboard.html.twig', array(
        
        'nbUser' => $nbUser,
        'nbContact' => $nbContact,
        'nbAressy' => $nbAressy
        
    ));
        
})
->bind('dashboard');

$app->run();