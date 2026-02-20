<?php
namespace ROG\Exceptions;
use ROG\Core\Game;

class UserException extends \Bga\GameFramework\UserException
{
    public function __construct($str)
    {
        parent::__construct($str);
    }
}
?>
