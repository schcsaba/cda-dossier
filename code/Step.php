<?php

namespace App\UploadSteps;


use App\Events\NextStepEvent;
use App\Events\StepErrorEvent;
use App\Helpers\Logger;
use App\Models\Upload;
use App\Models\UploadStep;
use App\Models\Utils\UploadStatus;
use App\UploadSteps\Utils\ShortErrorMessage;
use Exception;
use Illuminate\Support\Facades\Log;
use Psr\Log\LogLevel;

abstract class Step implements StepInterface
{
    public string $pathToPreviousResultIn;
    protected string $uploadId;
    protected string $uploadType;
    protected array $stepConfigParameters;
    protected string $pathToResultOut;
    protected array $transactionAndFileParameters;
    protected UploadStep $uploadStep;
    protected string $logFilePath;
    protected string $devLogFilePath;

    public function __construct(
        string $uploadId,
        $uploadType,
        array $stepConfigParam,
        array $transactionParameters,
        string $inIsPreviousStepOutput
    )
    {
        $this->uploadId = $uploadId;
        $this->uploadType = $uploadType;
        $this->stepConfigParameters = $stepConfigParam;

        $this->pathToPreviousResultIn = $inIsPreviousStepOutput;
        $this->pathToResultOut = $inIsPreviousStepOutput;

        $this->transactionAndFileParameters = $transactionParameters;
        $this->logFilePath = $this->getUploadId() . '/' . $this->getUploadId() . '_0_log';
        $this->devLogFilePath = $this->getUploadId() . '/' . $this->getUploadId() . '_0_dev_log';

        $this->uploadStep = UploadStep::create(
            [
                'upload_id'         => $this->uploadId,
                'path'              => $this->pathToPreviousResultIn,
                'step'              => get_class($this),
                'config_parameters' => $this->stepConfigParameters,
                'file_parameters'   => $this->transactionAndFileParameters,
                'path_result_in'    => $this->pathToPreviousResultIn,
                'path_result_out'   => $this->pathToPreviousResultIn,
                'status'            => UploadStatus::STARTED
            ]
        );
    }

    abstract function process();

    abstract function __toString();

    public function start()
    {
        Log::info(class_basename($this));

        Logger::appendLog($this->logFilePath, 'Step: ' . $this, LogLevel::INFO);
        Logger::appendLog($this->devLogFilePath, 'Step: ' . get_class($this), LogLevel::INFO);
        Logger::appendLog($this->logFilePath, 'start', LogLevel::INFO);
        Logger::appendLog($this->devLogFilePath, 'start', LogLevel::INFO);

        try {
            $this->uploadStep->setStatus(UploadStatus::PENDING);
            $this->process();
        } catch (Exception $e) {
            $this->uploadStep->setStatus(UploadStatus::ABORTED);
            $upload = Upload::findOrFail($this->getUploadId());
            $upload->setStatus(UploadStatus::ABORTED);
            $errorMessageArray = explode(ShortErrorMessage::GLUE, $e->getMessage(), 2);
            $shortErrorMessage = $errorMessageArray[0];

            Logger::appendLog($this->logFilePath, $shortErrorMessage, LogLevel::ERROR);
            $devLogMessage = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
            Logger::appendLog($this->devLogFilePath, $devLogMessage, LogLevel::ERROR);

            $message = ShortErrorMessage::FILE_CONTENT_ERROR;
            StepErrorEvent::dispatch($this, $message);

            report($devLogMessage);
            return;
        }
        $this->end();
    }

    protected function end()
    {
        Logger::appendLog($this->logFilePath, 'end' . PHP_EOL, LogLevel::INFO);
        Logger::appendLog($this->devLogFilePath, 'end' . PHP_EOL, LogLevel::INFO);
        $this->uploadStep->path_result_out = $this->pathToResultOut;
        $this->uploadStep->setStatus(UploadStatus::COMPLETED);
        NextStepEvent::dispatch($this);
    }

    public function getUploadId(): string
    {
        return $this->uploadId;
    }

    public function getUploadType(): string
    {
        return $this->uploadType;
    }

    public function getUploadCategory(): string
    {
        return explode('.', $this->uploadType)[0];
    }

    public function getUploadSubCategory(): string
    {
        return explode('.', $this->uploadType)[1];
    }

    public function getUploadStep(): UploadStep
    {
        return $this->uploadStep;
    }

    public function setUploadType($type): string
    {
        $this->uploadType = $type;
        return $this;
    }

    public function getStepConfigParameters(): array
    {
        return $this->stepConfigParameters;
    }

    public function getTransactionAndFileParameters(): array
    {
        return $this->transactionAndFileParameters;
    }

    public function setTransactionAndFileParameters(string $parameterKey, $parameterValue): void
    {
        $this->transactionAndFileParameters[$parameterKey] = $parameterValue;
        $this->uploadStep->file_parameters = $this->transactionAndFileParameters;
        $this->uploadStep->save();
    }

    public function getPreviousResult(): ?string
    {
        return StepResult::getPrevious($this->uploadId, $this->pathToPreviousResultIn);
    }

    public function putOutResult($stepResult)
    {
        if (!is_string($stepResult)) {
            $stepResult = json_encode($stepResult);
            if (!$stepResult) {
                throw new Exception('Failed to json_encode output: ' . json_last_error_msg());
            }
        }

        $this->pathToResultOut = StepResult::put($this, $stepResult);
    }

    public function getNameOfPreviousResultIn(): string
    {
        return $this->pathToPreviousResultIn;
    }

    public function getPathToPreviousResultIn(): string
    {
        return $this->uploadId . '/' . $this->pathToPreviousResultIn;
    }

    public function getNameResultOut(): string
    {
        return $this->pathToResultOut;
    }

    public function getPathToResultOut(): string
    {
        return $this->uploadId . '/' . $this->pathToResultOut;
    }

    public function getbasePath(): string
    {
        return $this->uploadId . '/';
    }

    public function getLogFilePath(): string
    {
        return $this->logFilePath;
    }

    public function getLogDetailsFilePath(): string
    {
        return $this->devLogFilePath;
    }
}