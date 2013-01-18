<?php
namespace Emk\Bundle\JukeboxBundle\Live;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Stream implements MessageComponentInterface
{
	protected $clients;

	/**
	 * Constructor
	 */
    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    /**
     * (non-PHPdoc)
     * @see \Ratchet\ComponentInterface::onOpen()
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $this->clients->attach($connection);
    }

    /**
     * (non-PHPdoc)
     * @see \Ratchet\MessageInterface::onMessage()
     */
    public function onMessage(ConnectionInterface $from, $message)
    {
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($message);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Ratchet\ComponentInterface::onClose()
     */
    public function onClose(ConnectionInterface $connection)
    {
        $this->clients->detach($connection);
    }

    /**
     * (non-PHPdoc)
     * @see \Ratchet\ComponentInterface::onError()
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $connection->close();
    }
}
