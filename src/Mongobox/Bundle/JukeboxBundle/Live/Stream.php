<?php
namespace Mongobox\Bundle\JukeboxBundle\Live;

use Monolog\Logger;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Stream implements MessageComponentInterface
{
    /**
     * @var \SplObjectStorage
     */
    protected $_clients;

    /**
     * @var \Monolog\Logger
     */
    protected $_logger;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_clients = new \SplObjectStorage;
        $this->_logger = new Logger('live-stream');
    }

    /**
     * (non-PHPdoc)
     * @see \Ratchet\ComponentInterface::onOpen()
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $this->_clients->attach($connection);
        $this->_logger->addInfo('A new connection is opened.');
    }

    /**
     * (non-PHPdoc)
     * @see \Ratchet\MessageInterface::onMessage()
     */
    public function onMessage(ConnectionInterface $from, $message)
    {
        foreach ($this->_clients as $client) {
            if ($from !== $client) {
                $client->send($message);
                $this->_logger->addDebug('New message sent.', array('message' => $message));
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Ratchet\ComponentInterface::onClose()
     */
    public function onClose(ConnectionInterface $connection)
    {
        $this->_clients->detach($connection);
        $this->_logger->addInfo('An old connection has been closed.');
    }

    /**
     * (non-PHPdoc)
     * @see \Ratchet\ComponentInterface::onError()
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        $connection->close();
        $this->_logger->addError('An error has occurred !', array('error' => $e->getMessage()));
    }
}
