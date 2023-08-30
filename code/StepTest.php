<?php

namespace Tests\Unit\UploadSteps;

use App\Console\Commands\UploadTrigger;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

abstract class StepTest extends TestCase
{
    use RefreshDatabase;

    private Upload $upload;

    private string $inputFileName = 'input';

    public function setUp(): void
    {
        parent::setUp();
        $this->upload = Upload::factory()->create();
        Storage::fake(UploadTrigger::$workDirectory);
    }

    public function getUpload(): Upload
    {
        return $this->upload;
    }

    public function setInputFileName(mixed $inputFileName): static
    {
        $this->inputFileName = $inputFileName;
        return $this;
    }

    public function getInputFileName(): string
    {
        return $this->inputFileName;
    }

    public function getStepResult($assoc = false)
    {
        return json_decode($this->getStepResultFile(), $assoc);
    }

    public function getStepResultFile(): ?string
    {
        return Storage::persistentFake(UploadTrigger::$workDirectory)->get($this->getOutputFilename());
    }

    public function createInputFile($data): bool
    {
        $path = $this->getUpload()->id . '/' . $this->inputFileName;
         return Storage::persistentFake(UploadTrigger::$workDirectory)->put($path, $data);
    }

    private function getOutputFilename(): string
    {
        return $this->getUpload()->id . '/' . $this->inputFileName . '_' . strtolower(str_replace('Test', '', class_basename($this)));
    }

    public function getLogFile()
    {
        return Storage::persistentFake(UploadTrigger::$workDirectory)->get($this->getUpload()->id . '/' . $this->getUpload()->id . '_0_log');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Storage::fake(UploadTrigger::$workDirectory);
    }
}
