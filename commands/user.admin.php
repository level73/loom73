<?php
namespace Loom73\Shuttle;

use Loom73\Beam\User;
use Loom73\Heddle\Session;
use Loom73\Shuttle\CLI;
use PDO;

class UserAdmin {
    protected string $table = 'auth_user';
    protected bool $dates = true;
    public function __construct() {
        $CLI = new CLI();

        $username = $CLI->ask('Enter the username: ');
        $email = $CLI->ask('Enter the email: ');
        $password = $CLI->ask('Enter the password: ');

        $salt = bin2hex(random_bytes(16));
        $combined_password = $password . $salt;
        $password = password_hash($combined_password, PASSWORD_ARGON2I);

        // Get Values
        $Data = [
            'username' =>   ['value' => $username, 'type' => PDO::PARAM_STR],
            'email' =>      ['value' => $email, 'type' => PDO::PARAM_STR],
            'password' =>   ['value' => $password, 'type' => PDO::PARAM_STR],
            'salt' =>       ['value' => $salt, 'type' => PDO::PARAM_STR],
            'status' =>     ['value' => STATUS_ACTIVE, 'type' => PDO::PARAM_INT],
            'role' =>       ['value' => 1, 'type' => PDO::PARAM_INT]
        ];

        $Model = new User();
        $user = $Model->create($Data);

        var_dump($user);

        if(!$user->success):

            echo $CLI->cout_color('Something went wrong, please try again later', 'red');
            print_r($Data);
            return false;
        else:
            $Session = new Session();
            $Session->createSession($user->insertId);
            echo $CLI->cout_color('User has been created', 'green');
            return true;
        endif;
    }
}