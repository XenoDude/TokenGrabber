<?php

namespace TokenGrabber\WebSocket\Message;

class Pong extends Message
{
    protected $opcode = 'pong';
}
