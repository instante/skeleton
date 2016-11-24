<?php
namespace App\Model\Examples;

use Doctrine\ORM\Mapping as ORM;

/**
 * Example concrete user class for Instante Doctrine user storage
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 *
 */
class User extends \Instante\Doctrine\Users\User
{
    /**
     * Note: Instante abstract User does not specify user identification column - it depends on
     * what the end application uses - username, e-mail or something else.
     *
     * Different name of identifier column should be specified as a second argument of
     * Instante\Doctrine\Users\Authenticator constructor.
     *
     * @ORM\Column(type="string", length=100)
     * @var string
     */
    private $name;
}
