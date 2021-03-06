CRM Aressy 2015 avec le Framework Silex
==========================================

A quoi sert le CRM Aressy ?
----------------------------
Le CRM Aressy est projet lancé interne par l'agence Aressy BtoB, qui à pour but de développer un web service pour la gestion de leurs clients ou plutôt de leurs futurs clients. Plus connu sous le nom de CRM: Customer Relationship Management, en anglais, soit en français « gestion de la relation client » ou « gestion des relations avec les clients » (GRC).

Technologies 
--------------
Il est basé sur le Framework Silex et la structure de ce web service est conçu avec "CRUD Admin Generator" (plus de détails en-dessous)

Fonctionnalité de base 
-------------------------

- Le CRM permet une connexion sécurisé et gére la gestion du multi-utilisateur (ADMIN_ROLE ou USER_ROLE)
- Permet d'ajouter des nouveaux utilisateurs
- Ajout/Lecture/Suppression/Modification des données relative aux clients
- Envoyer un emails au clients
- Des filtres avancés et perssonnalisé permettent de trié les clients

Installation 
--------------

Clonez le repository

	git clone https://github.com/barathpoubady/crm_aressy_2015_silex.git crm_aressy_2015_silex
	cd crm_aressy_2015_silex
	
Editer le fichier crm_aressy_2015_silex/src/app.php et ajouter vos données de connexion à partir de la ligne 68:

$app->register(new DoctrineServiceProvider(), array(

        'dbs.options' => array(
            'db' => array(
                'driver'   => 'pdo_mysql',
                'dbname'   => 'crudadmin',
                'host'     => 'localhost',
                'user'     => 'root',
                'password' => 'root',
                'charset'  => 'utf8',
            ),
        )
));

Changer la ligne suivante:

$app['asset_path'] = '/resources';
Avec votre nom de domaine ou url du localhost, par exemple:

$app['asset_path'] = 'http://domain.com/crm_aressy_2015_silex/web/resources';
ou 
$app['asset_path'] = 'http://localhost/crm_aressy_2015_silex/web/resources';

Etape finale ajouter la base de donnée 'crudadmin.sql' dans votre base de donnée.

Il ne vous reste plus que a vous connecter en vous rendent sur l'url suivante :

http://VOTRE_DOMAINE/crm_aressy_2015_silex/web/index.php/

et utiliser le login et mdp suivant pour vous connecter :

id : TEST3
mdp : password



============================ FIN ===============================


CRUD Admin Generator
===================

What is CRUD Admin Generator?
-----------------------------

**CRUD Admin Generator** ([http://crud-admin-generator.com][1]) is a tool to **generate a complete backend from a MySql database** where you can create, read, update and delete records in a database. 

**The backend is generated in seconds** without configuration files where there is a lot of *"magic"* and is very difficult to adapt to your needs. 

**The generated code is fully customizable and extensible.**

It has been programmed with the Silex framework, so the resulting code is PHP.


Installation
------------

Clone the repository

    git clone https://github.com/jonseg/crud-admin-generator.git admingenerator

    cd admingenerator

Download composer:

    curl -sS https://getcomposer.org/installer | php

Install vendors:

    php composer.phar install

You need point the document root of your virtual host to /path_to/admingenerator/web

This is an example of VirtualHost:

    <VirtualHost *:80>
        DocumentRoot /path_to/admingenerator/web
        DirectoryIndex index.php
        <Directory "/path_to/admingenerator/web">
            Options Indexes FollowSymLinks
            Order Allow,Deny
            Allow from all
            AllowOverride all
            <IfModule mod_php5.c>
                php_admin_flag engine on
                php_admin_flag safe_mode off
                php_admin_value open_basedir none
            </ifModule>
        </Directory>
    </VirtualHost>
    
You can customize the url using the .htaccess file, maybe this will help you:
[http://stackoverflow.com/questions/24952846/how-do-i-remove-the-web-from-my-url/24953439#24953439](http://stackoverflow.com/questions/24952846/how-do-i-remove-the-web-from-my-url/24953439#24953439)


Generate CRUD backend
---------------------

Edit the file /path_to/admingenerator/src/app.php and set your database conection data:

    $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
        'dbs.options' => array(
            'db' => array(
                'driver'   => 'pdo_mysql',
                'dbname'   => 'DATABASE_NAME',
                'host'     => 'localhost',
                'user'     => 'DATABASE_USER',
                'password' => 'DATABASE_PASS',
                'charset'  => 'utf8',
            ),
        )
    ));


You need to set the url of the resources folder.

Change this line:

    $app['asset_path'] = '/resources';

For the url of your project, for example:

    $app['asset_path'] = 'http://domain.com/crudadmin/resources';


Now, execute the command that will generate the CRUD backend:

    php console generate:admin

**This is it!** Now access with your favorite web browser.


The command generates one menu section for each database table. **Now will be much easier to list, create, edit and delete rows!**


Customize the result
--------------------

The generated code is fully configurable and editable, you just have to edit the corresponding files.

 - The **controller** you can find it in **/web/controllers/TABLE_NAME/index.php**
 - The **views** are in **/web/views/TABLE_NAME**

It has generated a folder for each database table.


Contributing
------------

If you want to contribute code to CRUD Admin Generator, we are waiting for your pull requests!

Some suggestions for improvement could be:

 - Different form fields depending on data type.: datetime, time...
 - Create admin user with a login and logout page.
 - Generate CRUD for tables with more than one primary key.
 - Any other useful functionality!

Author
------

* Jon Segador <info@jonsegador.com>
* Personal site: [http://jonsegador.com/](http://jonsegador.com/)
* Twitter: *[@jonseg](https://twitter.com/jonseg)*
* CRUD Admin Generator webpage: [http://crud-admin-generator.com](http://crud-admin-generator.com)


  [1]: http://crud-admin-generator.com
