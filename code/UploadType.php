<?php

namespace App\Models\Utils;


use App\UploadSteps\FuelTransaction\RedirectToNewConfig;
use App\UploadSteps\StepInterface;
use App\UploadSteps\StepStart;

class UploadType
{

    public const ROOT_PATH = 'uploads';

    private static $config;

    protected string $uploadTypeConfigPath;

    protected array $uploadConfigArray;

    public function __construct($uploadTypeConfigPath)
    {
        $this->uploadTypeConfigPath = $uploadTypeConfigPath;
        $this->uploadConfigArray = config(self::ROOT_PATH . '.' . $this->uploadTypeConfigPath);
        if ($this->uploadConfigArray == null) {
            throw new \Exception('Config type unknown');
        }
    }

    public function getUploadConfigArray()
    {
        return $this->uploadConfigArray;
    }

    public static function hasConfig($path)
    {
        return config(self::ROOT_PATH . '.' . $path) !== null;
    }

    public static function getUploadConfig($uploadTypeConfigPath)
    {
        self::$config = new UploadType($uploadTypeConfigPath);
        return self::$config;
    }

    public function getCurrentRankStep(StepInterface $step)
    {
        if ($step instanceof StepStart || $step instanceof RedirectToNewConfig) {
            $key = -1;
        } else {
            $class = get_class($step);
            $key = array_search($class, array_column($this->uploadConfigArray, 'class_namespace'));

            if ($key === false) {
                throw new \Exception('Step not found in configuration');
            }
        }

        return $key;
    }

    public function getCurrentStep(StepInterface $step)
    {
        $key = $this->getCurrentRankStep($step);
        return $this->uploadConfigArray[$key];
    }

    public function hasNextStep(StepInterface $step)
    {
        $key = $this->getCurrentRankStep($step);
        $next = $key + 1;
        $hasNextStep = array_key_exists($next, $this->uploadConfigArray);
        return $hasNextStep;
    }

    public function getNextStep(StepInterface $currentStep)
    {
        $nextStep = [];
        $key = $this->getCurrentRankStep($currentStep);
        $next = $key + 1;
        $hasNextStep = array_key_exists($next, $this->uploadConfigArray);
        if ($hasNextStep) {
            $nextStep = $this->uploadConfigArray[$next];
        }
        return $nextStep;
    }
}
