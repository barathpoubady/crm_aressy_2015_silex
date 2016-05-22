<?php
// app/src/App/User/UserProvider.php
namespace App;

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
 
class UserProvider implements UserProviderInterface
{
    private $conn;
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
        //echo '<pre/>';
        //var_dump($conn);die();
    }
 
    public function loadUserByUsername($username)
    {
        
        $stmt = $this->conn->executeQuery('SELECT * FROM user WHERE login = ?', array(strtolower($username)));
      
        if (!$user = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return new User($user['login'], $user['password'], explode(',', $user['role']), true, true, true, true);
    }
 
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
        return $this->loadUserByUsername($user->getUsername());
    }
 
    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}