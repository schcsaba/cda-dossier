    #[OA\Post(
        path: '/api/command/uploads/{category}/{type}',
        operationId: 'postUpload',
        summary: 'Post an Upload',
        tags: ['Upload - POST'],
        parameters: [
            new OA\Parameter(
                name: 'category',
                description: 'Upload category parameter',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                )
            ),
            new OA\Parameter(
                name: 'type',
                description: 'Upload type parameter',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                )
            ),
            new OA\RequestBody(
                required: true,
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        required: ['client_app', 'parameters', 'files'],
                        properties: [
                            'client_app' => new OA\Property(
                                property: 'client_app',
                                type: 'integer',
                                format: 'int32',
                                minimum: 0,
                                enum: [1, 23, 25]
                            ),
                            'parameters' => new OA\Property(
                                property: 'parameters',
                                required: ['client_id', 'user_id'],
                                properties: [
                                    'client_id' => new OA\Property(
                                        property: 'client_id',
                                        type: 'integer',
                                        format: 'int64',
                                        minimum: 0
                                    ),
                                    'user_id' => new OA\Property(
                                        property: 'user_id',
                                        type: 'integer',
                                        format: 'int64',
                                        minimum: 0
                                    )
                                ],
                                type: 'object'
                            ),
                            'files' => new OA\Property(
                                property: 'files',
                                type: 'array',
                                items: new OA\Items(
                                    required: ['content'],
                                    properties: [
                                        'content' => new OA\Property(
                                            property: 'content',
                                            type: 'string',
                                            minLength: 27,
                                            pattern: '^data:text/plain;base64,'
                                        )
                                    ],
                                    type: 'object'
                                ),
                                minItems: 1
                            )
                        ],
                        type: 'object'
                    )
                )
            )
        ],
        responses: [
            new OA\Response(response: 201, description: 'Success'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function upload(UploadStoreRequest $request, string $category, string $type): JsonResponse
    {
        Artisan::call(
        //'upload-trigger:' . $type,
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

        //TODO Manage response http code ??
        return new JsonResponse($o, 201);
    }