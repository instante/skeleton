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
}
