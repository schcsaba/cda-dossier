<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadStoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class UploadController extends Controller
{
    public function upload(UploadStoreRequest $request, string $category, string $type): JsonResponse
    {
        Artisan::call(
            'upload-' . $category . ':' . $type,
            [
                'token' => request()->header('Authorization'),
                'input' => $request->validated(),
                'category' => $category,
                'type' => $type
            ]
        );

        $o = Artisan::output();
        $o = json_decode($o, true);

        return new JsonResponse($o, 201);
    }
}