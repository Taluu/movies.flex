<?php
namespace App;

use Exception;

class MovieNotFoundException extends Exception
{
    private $id;

    public function __construct(int $id, Exception $previous = null)
    {
        $this->id = $id;

        parent::__construct("Movie {$id} was not found", 0, $previous);
    }

    public function getId(): int
    {
        return $id;
    }
}
