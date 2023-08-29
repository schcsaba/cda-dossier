<?php

namespace App\Listeners;

use App\Events\NextStepEvent;
use App\Events\StepErrorEvent;
use App\Models\Upload;
use App\Models\Utils\UploadStatus;
use App\Models\Utils\UploadType;
use App\UploadSteps\Step;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Pipeline\Pipeline;

use Illuminate\Queue\InteractsWithQueue;

class StepEventSubscriber implements ShouldQueue
{
    public const PROCESS_CONFIG_KEY = 'process';
    public const ERROR_CONFIG_KEY = 'error';

    public function handleNextStep(NextStepEvent $event): bool
    {
        /**@var Step $step */
        $step = $event->step;
        $transactionAndFileParameters = $step->getTransactionAndFileParameters();
        $uploadTypePath = $event->step->getUploadType();

        $uploadTypeConfig = UploadType::getUploadConfig($uploadTypePath . '.' . self::PROCESS_CONFIG_KEY);
        $nextStep = $uploadTypeConfig->getNextStep($step);

        if (!empty($nextStep)) {
            $stepConfigParameters = array_key_exists('parameters', $nextStep) ? $nextStep['parameters'] : [];
            $instanceStep = new $nextStep['class_namespace'](
                $event->step->getUploadId(),
                $event->step->getUploadType(),
                $stepConfigParameters,
                $transactionAndFileParameters,
                $event->step->getNameResultOut(),
            );
            $instanceStep->start();
        }

        if (empty($nextStep)) {
            /**@var Upload $upload */
            $upload = Upload::find($event->step->getUploadId());
            $upload->setStatus(UploadStatus::COMPLETED);
            $upload->save();
        }

        return true;
    }

    public function handleStepError(StepErrorEvent $stepErrorEvent): bool
    {
        /**@var Upload $upload */
        $upload = Upload::find($stepErrorEvent->step->getUploadId());
        $upload->setStatus(UploadStatus::ABORTED); // ? already done ?

        $uploadTypePath = $stepErrorEvent->step->getUploadType();
        $hasConfig = UploadType::hasConfig($uploadTypePath . '.'.self::ERROR_CONFIG_KEY);

        if ($hasConfig) {
            $uploadTypeConfig = UploadType::getUploadConfig($uploadTypePath . '.error');
            $errorTasks = array_column($uploadTypeConfig->getUploadConfigArray(), 'class_namespace');

            app(Pipeline::class)->send($stepErrorEvent)
                ->through(
                    $errorTasks
                )->then(function ($stepErrorEvent) { return true; });
        }
        return true;
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            NextStepEvent::class,
            [StepEventSubscriber::class, 'handleNextStep']
        );

        $events->listen(
            StepErrorEvent::class,
            [StepEventSubscriber::class, 'handleStepError']
        );
    }
}