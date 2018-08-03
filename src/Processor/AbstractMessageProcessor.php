<?php
namespace NeedleProject\LaravelRabbitMq\Processor;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AbstractMessageProcessor
 *
 * @package NeedleProject\LaravelRabbitMq\Processor
 * @author  Adrian tilita <adrian@tilita.ro>
 * @todo    Add Logger
 */
abstract class AbstractMessageProcessor implements MessageProcessorInterface
{
    /**
     * @var int
     */
    private $messageCount = 0;

    /**
     * {@inheritdoc}
     * @param AMQPMessage $message
     */
    public function consume(AMQPMessage $message)
    {
        $this->messageCount++;
        try {
            $response = $this->processMessage($message);
            if ($response === true) {
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            } else {
                $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag'], false, true);
            }
        } catch (\Exception $e) {
            $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag'], false, true);
        }
    }

    /**
     * @return int
     */
    public function getProcessedMessages(): int
    {
        return $this->messageCount;
    }

    /**
     * @param AMQPMessage $message
     * @return bool
     */
    abstract public function processMessage(AMQPMessage $message): bool;
}
