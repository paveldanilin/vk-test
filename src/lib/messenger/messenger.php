<?php
namespace vk\lib\messenger;

require_once __DIR__ . '/exception/MessengerException.php';
require_once __DIR__ . '/Message.php';
require_once __DIR__ . '/DurableMessage.php';

use AMQPChannel;
use AMQPConnection;
use AMQPConnectionException;
use AMQPEnvelope;
use AMQPExchange;
use AMQPQueue;
use AMQPQueueException;
use vk\lib\Context;

function get_conn_config(Context $context): array
{
    $host = ($context->getValue('config') ?? [])['message_bus']['host'] ?? 'localhost';
    $user = ($context->getValue('config') ?? [])['message_bus']['user'] ?? 'guest';
    $pass = ($context->getValue('config') ?? [])['message_bus']['pass'] ?? 'guest';
    return [$host, $user, $pass];
}

/**
 * @throws MessengerException
 */
function publish_message(Message $message, Context $context): bool
{
    [$host, $user, $pass] = get_conn_config($context);

    try {
        $message_payload = \json_encode($message->getPayload(), JSON_THROW_ON_ERROR);

        $connection = new AMQPConnection();
        $connection->setHost($host);
        $connection->setLogin($user);
        $connection->setPassword($pass);
        $connection->connect();

        $channel = new AMQPChannel($connection);
        $exchange = new AMQPExchange($channel);

        $queue = new AMQPQueue($channel);
        $queue->setName($message->getQueue());
        if ($message instanceof DurableMessage) {
            $queue->setFlags(AMQP_DURABLE);
        } else {
            $queue->setFlags(AMQP_NOPARAM);
        }
        $queue->declareQueue();

        $attributes = [
            'headers' => \array_merge(
                $message->getHeaders(),
                [
                    '@class' => \get_class($message->getPayload()),
                    '@user_id' => $context->getValue('user_id'),
                ]
            ),
        ];
        if ($message instanceof DurableMessage) {
            $attributes['delivery_mode'] = 2;
        }

        $ret = $exchange->publish($message_payload, $message->getQueue(), AMQP_NOPARAM, $attributes);
        $connection->disconnect();

        return $ret;
    } catch (\Throwable $exception) {
        throw new MessengerException(\sprintf('Could not publish message: %s', $exception->getMessage()), $exception);
    }
}

function consume_messages(array $options, Context $context, array $consumers): void
{
    $host = $options['host'] ?? 'localhost';
    $user = $options['user'] ?? 'guest';
    $pass = $options['pass'] ?? 'guest';
    $queue_name = $options['queue_name'] ?? '';
    $limit = $options['limit'] ?? 50;

    $messages_count = 0;

    $handler_wrapper = static function (AMQPEnvelope $envelope, AMQPQueue $queue) use($consumers, $context, &$messages_count, $limit) {

        try {
            $class_name = $envelope->getHeader('@class');
            if (false === $class_name) {
                throw new \RuntimeException('Not found `@class` in headers');
            }

            $user_id = $envelope->getHeader('@user_id');
            if (false !== $user_id) {
                $context->setValue('user_id', $user_id);
            }

            $consumer_class = $consumers[$class_name] ?? null;
            if (null === $consumer_class) {
                throw new \RuntimeException(\sprintf('Consumer not found for `%s`', $class_name));
            }

            if (!\method_exists($class_name, 'fromArray')) {
                throw new \RuntimeException(\sprintf('Not found factory method `%s::fromArray`', $class_name));
            }

            echo "> [$messages_count] New Message: $class_name => $consumer_class" . PHP_EOL;

            $payload_data = \json_decode($envelope->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $factory = $class_name . '::fromArray';

            $msg = $factory($payload_data);

            $consumer = new $consumer_class;
            $consumer($msg, $context);

            $queue->ack($envelope->getDeliveryTag());

            $messages_count++;
        } catch (\Throwable $exception) {
            $queue->nack($envelope->getDeliveryTag());
            throw $exception;
        }

        if ($messages_count >= $limit) {
            trigger_error('Message limit exceeded, not a problem just restart :)', E_USER_ERROR);
        }
    };

    $retry = $options['retry'] ?? 1;
    $retry_timeout = $options['retry_timeout'] ?? 5;
    $connection = null;
    echo "Connecting to `$host`...\n";
    for ($i = 0; $i < $retry; $i++) {
        try {
            $connection = new AMQPConnection();
            $connection->setHost($host);
            $connection->setLogin($user);
            $connection->setPassword($pass);
            $connection->connect();
            echo "Connected!\n";
            break;
        } catch (AMQPConnectionException $exception) {
            if ($i >= $retry - 1) {
                echo "Connection failed: " . $exception->getMessage() . "\n";
                throw $exception;
            }
            $left = $retry - $i;
            $attempt = $i+1;
            echo "Could not connect to `$host` with attempt $attempt, $left left: " . $exception->getMessage() . "\n";
            echo "Sleep `$retry_timeout` seconds...\n";
            \sleep($retry_timeout);
        }
    }

    try{
        $channel = new AMQPChannel($connection);

        $queue = new AMQPQueue($channel);
        $queue->setName($queue_name);
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();

        echo '[' . $queue_name . ':' . $limit . '] Waiting for messages. To exit press CTRL+C ', PHP_EOL;
        $queue->consume($handler_wrapper);

        echo 'Close connection...', PHP_EOL;
        $queue->cancel();
        $connection->disconnect();
    }catch(\Exception $ex){
        print_r($ex);
        throw $ex;
    }
}
