<?php

namespace Kim1ne\Socket;

use Kim1ne\InputMessage;
use Kim1ne\Loop\LoopServer;
use Kim1ne\Looper;
use Kim1ne\Socket\Transport\TransportInterface;
use Kim1ne\Socket\Transport\Udp;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;

class Server implements Looper
{
    public static bool $serverIsCreate = false;

    /**
     * @var resource $server
     */
    private $server;

    private TransportInterface $transport;

    /**
     * @var Connection[]
     */
    private array $connections = [];

    private array $events = [];

    private ?LoopInterface $loop = null;

    private readonly Context $context;

    private readonly bool $permanentConnection;

    public function __construct(
        Transport     $transport = Transport::WS,
        public string $host = '0.0.0.0',
        public int    $port = 2346,
        array $serverContext = []
    )
    {
        if (self::$serverIsCreate) {
            throw new \Exception("Server already created");
        }

        self::$serverIsCreate = true;

        $this->transport = $transport->get();

        $this->createContext($serverContext);

        $this->createServer($host, $port);
    }

    public function getStreamServer()
    {
        return $this->server;
    }

    private function createContext(array $serverContext): void
    {
        $context = new Context();
        $this->context = $context;
        $this->transport->setContext($context);

        $this->permanentConnection = $this->transport->permanentConnection();

        if (!empty($serverContext)) {
            $context->server = $serverContext;
        }
    }

    private function createServer(string $host, string $port): void
    {
        $server = $this->transport->createServer($host, $port);

        $this->server = $server;

        stream_set_blocking($server, false);
    }

    public function setLoop(LoopInterface $loop): static
    {
        $this->loop = $loop;
        return $this;
    }

    public function getLoop(): LoopInterface
    {
        if ($this->loop === null) {
            $this->loop = Loop::get();
        }
        return $this->loop;
    }

    public function on(string $event, callable $callback): static
    {
        $this->events[$event] = $callback;

        return $this;
    }

    private function callEvent(string $eventType, array $params): void
    {
        $callback = $this->events[$eventType] ?? null;

        if (is_callable($callback) === false) {
            return;
        }

        try {
            call_user_func_array($callback, $params);
        } catch (\Throwable $throwable) {
            InputMessage::red('Error: ' . $throwable->getMessage());

            try {
                $this->callEvent('error', [$throwable]);
            } catch (\Throwable $throwable) {

            }
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function run(): void
    {
        InputMessage::green('Listen to ' . $this->transport->getListenAddress($this->host, $this->port));

        $loop = $this->getLoop();

        $loop->addReadStream($this->server, function ($server) use ($loop) {
            $clientSocket = $this->transport->readServer($this);

            if ($clientSocket === false) {
                return;
            }

            $this->handleClientConnection($clientSocket, $loop);
        });

        if (LoopServer::isStart() === false) {
            $loop->run();
            $this->close($this->server);
        }
    }

    private function handleClientConnection($clientSocket, LoopInterface $loop): void
    {
        $this->transport->prepare($clientSocket, $loop, function ($result) use ($clientSocket, $loop) {
            if ($result === false) {
                $loop->removeReadStream($clientSocket);
                $this->close($clientSocket);
                return;
            }

            $this->handleClientMessages($clientSocket, $loop);
        });
    }

    private function handleClientMessages($clientSocket, $loop): void
    {
        if ($this->permanentConnection) {
            $this->readStream($clientSocket, $loop, $this->connection($clientSocket));
            return;
        }

        $message = $clientSocket;

        $this->handleMessage(
            new Connection($message, $this->transport),
            $message
        );
    }

    private function handleMessage(Connection $connection, $message)
    {
        $this->message($connection, $message);
    }

    private function readStream($clientSocket, LoopInterface $loop, Connection $connection): void
    {
        $loop->addReadStream($clientSocket, function ($clientSocket) use ($loop, $connection) {
            $message = $this->transport->readClient($clientSocket);

            if ($message === false || $message === '' || feof($clientSocket)) {
                $loop->removeReadStream($clientSocket);
                $this->close($connection);
                return;
            }

            $this->handleMessage($connection, $message);
        });
    }

    /**
     * @return Connection[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * @param resource $socket
     * @return Connection
     */
    private function connection($socket): Connection
    {
        $socketId = (int)$socket;

        if (isset($this->connections[$socketId])) {
            return $this->connections[$socketId];
        }

        $connection = $this->transport->connection($socket);

        $this->connections[$socketId] = $connection;

        $this->callEvent(__FUNCTION__, [$connection, $this]);

        return $connection;
    }

    /**
     * @param Connection|resource $connection
     * @return void
     */
    private function close($connection): void
    {
        if (is_resource($connection)) {
            fclose($connection);
            return;
        }

        $socket = $connection->getSocket();

        if (!is_resource($socket)) {
            return;
        }

        $socketId = (int)$socket;

        if (!empty($this->connections[$socketId])) {
            unset($this->connections[$socketId]);
        } else {
            $connection->close();
        }

        $this->callEvent(__FUNCTION__, [$this]);
    }

    private function message(Connection $connection, string $message): void
    {
        $message = $this->transport->decode($message);
        $this->callEvent(__FUNCTION__, [new Message($message), $connection, $this]);
    }

    public function sendAll(string|Message $message): void
    {
        $this->sendAllWrap($message, function () {
            return true;
        });
    }

    private function sendAllWrap(string|Message $message, callable $noSkip): void
    {
        $this->context->messageEncode = false;

        $message = $this->transport->encode($message);

        foreach ($this->connections as $key => $connection) {
            if ($noSkip($key, $connection)) {
                $connection->send($message);
            }
        }

        $this->context->messageEncode = true;
    }

    public function sendAllButNotToHim(Connection $connection, string|Message $message): void
    {
        $this->sendAllWrap($message, function ($key, Connection $conn) use ($connection) {
            return $conn !== $connection;
        });
    }

    public function sendChoice(string|Message $message, callable $callback): void
    {
        $this->sendAllWrap($message, function ($key, Connection $connection) use ($callback) {
            return $callback($connection);
        });
    }
}