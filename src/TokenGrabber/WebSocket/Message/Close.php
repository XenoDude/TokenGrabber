<?php

namespace TokenGrabber\WebSocket\Message;

class Close extends Message
{
    protected $opcode = 'close';
}
