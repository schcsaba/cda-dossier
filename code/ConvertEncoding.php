<?php

namespace App\UploadSteps;


use App\Helpers\Logger;
use App\UploadSteps\Utils\ShortErrorMessage;
use Exception;
use Psr\Log\LogLevel;

class ConvertEncoding extends Step
{
    function process()
    {
        Logger::appendLog($this->logFilePath, 'process', LogLevel::INFO);
        Logger::appendLog($this->devLogFilePath, 'process', LogLevel::INFO);

        if(!isset($this->stepConfigParameters['from']) || !isset($this->stepConfigParameters['to'])){
            throw new Exception(ShortErrorMessage::SERVER_ERROR . ShortErrorMessage::GLUE . ': Parameter missing for step ' . get_class($this));
        }

        $content = $this->getPreviousResult();

        $convertContent = iconv($this->stepConfigParameters['from'], $this->stepConfigParameters['to'], $content);
        $this->putOutResult($convertContent);

        Logger::appendLog($this->logFilePath, 'The encoding is successfully converted from '. $this->stepConfigParameters['from'] .' to '. $this->stepConfigParameters['to'] .'.', LogLevel::INFO);
        Logger::appendLog($this->devLogFilePath, 'The encoding is successfully converted from '. $this->stepConfigParameters['from'] .' to '. $this->stepConfigParameters['to'] .'.', LogLevel::INFO);
    }

    function __toString()
    {
        return 'Convert encoding...';
    }
}