<?php

namespace App\Service;

use RdKafka\Conf;
use RdKafka\TopicConf;

class KafkaService
{

    private function getConf(): Conf
    {
        $config = new Conf();
        $config->setRebalanceCb(function (
            \RdKafka\KafkaConsumer $kafka,
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
                    throw new \Exception($err);
            }
        });
        $config        = new Conf();
        $host        = $_ENV['APP_KAFKA_SERVICE_HOST'];
        $port        = $_ENV['APP_KAFKA_SERVICE_PORT'];
        $configArray = [
            'group.id' => 'group_1',
            'metadata.broker.list' => "$host:$port",
            'enable.auto.commit' => 'false',
        ];
        foreach ($configArray as $key => $value) {
            $config->set($key, $value);
        }

        $topicConf = new TopicConf();
        $topicConf->set('auto.offset.reset', 'smallest');

        $config->setDefaultTopicConf($topicConf);

        return $config;
    }

    public function getConsumer(): \RdKafka\KafkaConsumer
    {
        return new \RdKafka\KafkaConsumer($this->getConf());
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
                    $eventPayload = json_decode($message->payload, true);
                    $this->log('pt -- '.$message->partition);
                    $this->log($eventPayload);
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
                    throw new \Exception($message->errstr(), $message->err);
            }
        }
    }

    private function log($string)
    {
        print_r($string);
        echo PHP_EOL;
    }
}