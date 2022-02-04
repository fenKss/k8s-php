<?php

namespace App\Service;

use Exception;
use RdKafka\Conf;
use App\Events\Event;
use RdKafka\TopicConf;
use RdKafka\KafkaConsumer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class KafkaService
{

    private Conf               $config;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->initConfig();
        $this->container = $container;
    }

    private function getConf(): Conf
    {
        return $this->config;
    }

    public function getConsumer(): KafkaConsumer
    {
        return new KafkaConsumer($this->getConf());
    }

    /**
     * @throws \RdKafka\Exception
     */
    public function listen(array $topics)
    {
        $consumer = $this->getConsumer();
        $consumer->subscribe($topics);

        while (true) {
            $message = $consumer->consume(3e3);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:

                    $this->log('pt -- '.$message->partition);
                    $this->log($message->payload);
                    $this->handlePayload($message->topic_name, $message->payload);
                    // Save offset
                    $consumer->commit($message);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    sleep(2);
                    $this->log('sleep');
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    //$this->log($message->errstr());
                    break;
                default:
                    throw new Exception($message->errstr(), $message->err);
            }
        }
    }

    private function log($string)
    {
        print_r($string);
        echo PHP_EOL;
    }

    /**
     * @return void
     */
    private function initConfig(): void
    {
        $this->config = new Conf();

        $configArray = [
            'group.id' => 'group_1',
            'metadata.broker.list' => $_ENV["KAFKA_URL"],
            'enable.auto.commit' => 'false',
        ];

        foreach ($configArray as $key => $value) {
            $this->config->set($key, $value);
        }

        $this->initTopicConf();
        $this->initRebalanceCb();
    }

    /**
     * @return void
     */
    private function initTopicConf(): void
    {
        $topicConf = new TopicConf();
        $topicConf->set('auto.offset.reset', 'smallest');
        $this->config->setDefaultTopicConf($topicConf);
    }

    /**
     * @return void
     */
    private function initRebalanceCb(): void
    {
        $this->config->setRebalanceCb(function (
            KafkaConsumer $kafka,
            $err,
            array $partitions = null
        ) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    foreach ($partitions as $partition) {
                        $this->log("Assign topic: {$partition->getTopic()}");
                    }

                    $kafka->assign($partitions);
                    break;
                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    foreach ($partitions as $partition) {
                        $this->log("Revoke topic: {$partition->getTopic()}");
                    }
                    $kafka->assign();
                    break;
                default:
                    throw new Exception($err);
            }
        });
    }

    private function handlePayload(string $topic, string $payload): void
    {
        try {
            $event = $this->getEventFromPayload($payload);
            $event->setTopic($topic);
            $method = lcfirst($event->getName())."Event";
            $this->getController($event)->$method($event);
        } catch (\Throwable $e) {
            $this->log($e->getMessage());
            return;
        }
    }

    /**
     * @throws \Exception
     */
    private function getEventFromPayload(string $payload): Event
    {
        $eventPayload = json_decode($payload, true);
        $event        = new Event($eventPayload);
        if (!$event->getName()) {
            throw new Exception("Event name not found");
        }
        return $event;
    }

    private function getController(Event $event): ?object
    {
        $topicName = ucfirst($event->getTopic());
        $nameSpace = "App\\Events\\Handler\\{$topicName}Handler";

        return $this->container->get($nameSpace);
    }
}