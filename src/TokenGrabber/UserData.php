<?php

namespace TokenGrabber;

class UserData
{

    /**
     * @var string
     */
    public $user;

    /**
     * @var string
     */
    public $token;

    public function __construct(string $user, $token) {
        $this->user = $user;
        $this->token = $token;
    }

}