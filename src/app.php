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

class queryData {
    public $start;
    public $recordsTotal;
    public $recordsFiltered;
    public $data;

    function queryData() {
    }
}


require_once '../app/UserProvider.php';

use App\UserProvider;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();

$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../web/views',
));
$app->register(new FormServiceProvider());
$app->register(new TranslationServiceProvider(), array(
    'translator.messages' => array(),
));
$app->register(new SwiftmailerServiceProvider());

/*, array(
   'swiftmailer.options' => array(
        'host' => 'localhost',
        'port' => '',
        'username' => '',
        'password' => '',
        'encryption' => null,
        'auth_mode' => null
    )
)*/


$app->register(new ValidatorServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SessionServiceProvider());

/* ### CONNEXION A BDD ### */

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


/*##### ENLEVER CETTE PARTIE EN DESSOUS POUR REGENERER LES TABLES ####*/

/** AUTHENTIFICATION **/
$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'foo' => array('pattern' => '^/propos'), // Exemple d'une url accessible en mode non connecté
        'default' => array(
            'pattern' => '^.*$',
            'anonymous' => true, // Indispensable car la zone de login se trouve dans la zone sécurisée (tout le front-office)
            'form' => array('login_path' => '/', 'check_path' => '/connexion'),
            'logout' => array('logout_path' => '/deconnexion'), // url à appeler pour se déconnecter
            'users' => $app->share(function() use ($app) {
                
                // La classe Ipf\UserProvider est spécifique à notre application et est décrite plus bas
                return new UserProvider($app['db']);
            }),
        ),
    ),
    'security.access_rules' => array(
        // ROLE_USER est défini arbitrairement, vous pouvez le remplacer par le nom que vous voulez
        array('^/.+$', array('ROLE_ADMIN' ,'ROLE_USER')),
        array('^/propos$', ''), // Cette url est accessible en mode non connecté
    )
));

           
/* Init l'authentification */
$app->boot();


/** Page Home/login **/
$app->get('/', function(Request $request) use ($app) {
    
     //var_dump($app['security']->isGranted('ROLE_ADMIN'));die();
    
   // On Verifie que l'on est pas deja logué, si c'est le cas on redirige sur le dashboard
   if ($app['security']->isGranted('ROLE_ADMIN') || $app['security']->isGranted('ROLE_USER')) {

      /* $app['userInfo'] = $app->share(function() use ($app) {

            $token = $app['security']->getToken();
            $user = $token->getUser(); //->getUserName()

            return $user;
                
        });

        /* PASSE EN SESSION LE CURRENT USER */
        /*$app['session']->set('user', $app['userInfo']);*/

       return $app->redirect('dash');
   }
   
   //return "ok";
   
   // sinon on affiche le form de login
   return $app['twig']->render('/login/login.html.twig', array(
       'error' => $app['security.last_error']($request),
       'last_username' => $app['session']->get('_security.last_username'),
   ));
   
})
->bind('login');

  /* RECUPERE LES INFOS DU ADMIN/USER */

/*##### ENLEVER CETTE PARTIE AU DESSUS POUR REGENERER LES TABLES ####*/

$app->before(function(Request $request) use ($app) { 

    
    if($app['security']->getToken() == NULL){
        
        
        
    }else{
        
        if($app['session']->get('user') == NULL) {
       
            // On Verifie que l'on est pas deja logué, si c'est le cas on redirige sur le dashboard
            if ($app['security']->isGranted('ROLE_ADMIN') || $app['security']->isGranted('ROLE_USER')) {

                $app['userInfo'] = $app->share(function() use ($app) {

                     $token = $app['security']->getToken();
                     $user = $token->getUser(); //->getUserName()

                     return $user;

                 });
                 
                 
                 $app['userInfo_2'] = $app->share(function() use ($app) {

                    $token = $app['security']->getToken();
                    $user = $token->getUser()->getUserName();        
                     
                     $find_sql = "SELECT * FROM `user` WHERE `login` = ?";
                     $row_sql = $app['db']->fetchAssoc($find_sql, array($user));

                     if($row_sql){

                        return $row_sql; 

                     }
                     
                     
                     return "Anonyme";

                 });

                 /* PASSE EN SESSION LE CURRENT USER */
                 $app['session']->set('user', $app['userInfo']);

                 $app['userInfo'] = $app['session']->get('user');
                 
                 $app['session']->set('user_2', $app['userInfo_2']); 

                 $app['userInfo_2'] = $app['session']->get('user_2');

                 //return $app->redirect('dash');
            }
       
        }
        
    }

});

$app['userInfo'] = $app['session']->get('user');
$app['userInfo_2'] = $app['session']->get('user_2');

$app['asset_path'] = 'http://localhost/crm_aressy_2015_silex/web/resources';
$app['debug'] = true;

return $app;
