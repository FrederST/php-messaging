<?php

namespace E4\Messaging\Test\Unit;

use E4\Messaging\AMQPConnection;
use E4\Messaging\Facades\Messaging;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use Tests\TestCase;

class ConsumerTest extends TestCase
{
    private string $exchange = 'direct_exchange';
    private string $queue = 'queue';

    public function test_consume_message()
    {
        $connection = new AMQPConnection(config('messagingapp.connections.rabbitmq'));
        $channel = $connection->getChannel();
        $channel->queue_declare($this->queue, false, true, false, false);
        $channel->exchange_declare($this->exchange, AMQPExchangeType::DIRECT, false, true, false);
        $channel->queue_bind($this->queue, $this->exchange);
        $message = new AMQPMessage('data1');
        $channel->basic_publish($message, $this->exchange);
        Messaging::consume(function (AMQPMessage $message) {
            $this->assertEquals($message->body, 'data1');
            $message->ack();
        });
    }
}
