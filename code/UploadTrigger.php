<?php

namespace App\Console\Commands;


use App\Events\NextStepEvent;
use App\Events\StepErrorEvent;
use App\Helpers\ErrorResponseSender;
use App\Helpers\Logger;
use App\Http\Resources\UploadResource;
use App\Http\UploadResponse;
use App\Models\Upload;
use App\Models\Utils\UploadStatus;
use App\UploadSteps\StepStart;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

abstract class UploadTrigger extends Command
{
    public static string $storageDirectory = 'storage_directory';
    public static string $workDirectory = 'work_directory';
    private string $logFilePath;

    public function __construct()
    {
        parent::__construct();

        $inputArgument = new InputArgument('input', InputArgument::REQUIRED, 'desc');
        $authorizationArgument = new InputArgument('token', InputArgument::REQUIRED, 'Token authorization');
        $categoryArgument = new InputArgument('category', InputArgument::REQUIRED, 'Category of the upload');
        $typeArgument = new InputArgument('type', InputArgument::REQUIRED, 'Type of the upload');
        $definition = new InputDefinition([$authorizationArgument, $inputArgument, $categoryArgument, $typeArgument]);
        $this->setDefinition($definition);
    }

    public function handle(): void
    {
        $input = $this->argument('input');

        $upload = $this->recordNewUpload($input);

        $transactionAndFileParam = array_merge(
            ['upload_type' => $this->argument('category') . '.' . $this->argument('type')],
            $input['parameters'],
            array_key_exists('parameters', $input['files'][0]) ? $input['files'][0]['parameters'] : [],
            ['token' => $this->argument('token')]
        );

        $stepParamIn = $upload->id . '_0_';

        NextStepEvent::dispatch(new StepStart($upload->id, $upload->upload_type, [], $transactionAndFileParam, $stepParamIn));

        $upload->setStatus(UploadStatus::STARTED);
        $uploadResource = new UploadResource($upload->append('UploadStatus')->only(['id', 'status', 'UploadStatus']));

        Logger::appendLog($this->logFilePath, $uploadResource->response()->content(), LogLevel::INFO);
        $this->info($uploadResource->response()->content());
    }


    public function recordNewUpload(array $input): Upload
    {
        $upload = Upload::create(
            [
                'parameters'  => $input['parameters'],
                'client_app'  => (int)$input['client_app'],
                'upload_type' => $this->argument('category') . '.' . $this->argument('type')
            ]
        );

        $this->logFilePath = $upload->id . '/' . $upload->id . '_log';

        $result = $this->saveInDirectories($upload, $input);
        $success = 0 === count(array_keys($result, false));
        if (false === $success) {
            $upload->setStatus(UploadStatus::ABORTED);
            $message = 'File save failed.';
            $code = 500;
            $uploadResponse = new UploadResponse($upload->id, $upload->status, $message, $result, $code);
            Logger::appendLog($this->logFilePath, $uploadResponse->getUploadJsonResponse(), LogLevel::ERROR);
            ErrorResponseSender::sendErrorResponse($message, $code);
        }
        return $upload;
    }

    public function saveInDirectories(Upload $upload, array $input): array
    {
        $inputFiles = $input['files'];
        $uploadParameters = $input['parameters'];
        $result = [];
        $payload = json_encode($input);

        $basePathForUpload = $upload->id . '/';
        $saved = Storage::disk(self::$storageDirectory)->put($upload->id . '/' . $upload->id . '_payload.json', $payload);
        $result['payload'] = $saved;

        foreach ($inputFiles as $key => $file) {
            $content = $file['content'];
            $content = str_replace('data:text/plain;base64,', '', $content);
            $content = base64_decode($content);

            $basePath = $upload->id . '/';

            $contentPath = $basePath . '/' . $upload->id . '_' . $key . '_';
            $contentSavedInWorkDir = Storage::disk(self::$workDirectory)
                ->put($contentPath, $content);

            $paramPath = $contentPath . '_parameters';
            $contentSavedInStorageDir = Storage::disk(self::$storageDirectory)->put($contentPath, $content);
            if (array_key_exists('parameters', $file)) {
                $paramSavedInStorageDir = Storage::disk(self::$storageDirectory)->put($paramPath, json_encode($file['parameters']));
            }
            $saved = true === $contentSavedInWorkDir && true === $contentSavedInStorageDir;
            $result['file_' . $key] = $saved;
        }
        return $result;
    }
}