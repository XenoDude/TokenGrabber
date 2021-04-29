<?php

namespace TokenGrabber\WebSocket\Message;

class Ping extends Message
{
    protected $opcode = 'ping';
}
