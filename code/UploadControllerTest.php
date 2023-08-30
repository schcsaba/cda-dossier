<?php

namespace Tests\Feature;

use Tests\TestCase;

class UploadControllerTest extends TestCase
{

    public function testNoClientAppKey(): void
    {
        $response = $this->postJson('/api/command/uploads/onboarding/drivers', [
            'parameters' => [
                'client_id' => 1,
                'user_id' => 10831
            ]
        ]);
        $response->assertUnprocessable();
    }

    public function testNoClientAppValue(): void
    {
        $response = $this->postJson('/api/command/uploads/onboarding/drivers', [
            ['client_app' => '  ']
        ]);
        $response->assertUnprocessable();
    }

    public function testUnknownClientApp(): void
    {
        $allowedClientApps = config('allowed_client_app');
        while (in_array($badClientApp = rand(1, 50), $allowedClientApps)) ;
        $response = $this->postJson('/api/command/uploads/onboarding/drivers', ['client_app' => $badClientApp]);

        $response->assertUnprocessable();
    }

    public function testNoParametersKey(): void
    {
        $allowedClientApps = array_flip(config('allowed_client_app'));
        shuffle($allowedClientApps);
        $response = $this->postJson('/api/command/uploads/onboarding/drivers', ['client_app' => array_pop($allowedClientApps)]);

        $response->assertUnprocessable();
    }

    public function testParametersValueNotArray(): void
    {
        $allowedClientApps = array_flip(config('allowed_client_app'));
        shuffle($allowedClientApps);
        $response = $this->postJson('/api/command/uploads/onboarding/drivers',
            [
                'client_app' => array_pop($allowedClientApps),
                'parameters' => 1
            ]
        );

        $response->assertUnprocessable();
    }

    public function testParametersValueEmptyArray(): void
    {
        $allowedClientApps = array_flip(config('allowed_client_app'));
        shuffle($allowedClientApps);
        $response = $this->postJson('/api/command/uploads/onboarding/drivers',
            [
                'client_app' => array_pop($allowedClientApps),
                'parameters' => []
            ]
        );

        $response->assertUnprocessable();
    }

    public function testNoFilesKey(): void
    {
        $allowedClientApps = array_flip(config('allowed_client_app'));
        shuffle($allowedClientApps);
        $response = $this->postJson('/api/command/uploads/onboarding/drivers',
            [
                'client_app' => array_pop($allowedClientApps),
                'parameters' => [
                    'client_id' => 1,
                    'user_id' => 10831
                ]
            ]
        );
        $response->assertUnprocessable();
    }

    public function testFilesValueNotArray(): void
    {
        $allowedClientApps = array_flip(config('allowed_client_app'));
        shuffle($allowedClientApps);
        $response = $this->postJson('/api/command/uploads/onboarding/drivers',
            [
                'client_app' => array_pop($allowedClientApps),
                'parameters' => [
                    'client_id' => 1,
                    'user_id' => 10831
                ],
                'files' => 'text'
            ]
        );
        $response->assertUnprocessable();
    }

    public function testFilesValuEmptyArray(): void
    {
        $allowedClientApps = array_flip(config('allowed_client_app'));
        shuffle($allowedClientApps);
        $response = $this->postJson('/api/command/uploads/onboarding/drivers',
            [
                'client_app' => array_pop($allowedClientApps),
                'parameters' => [
                    'client_id' => 1,
                    'user_id' => 10831
                ],
                'files' => []
            ]
        );
        $response->assertUnprocessable();
    }

    public function testNoFilesContentKey(): void
    {
        $allowedClientApps = array_flip(config('allowed_client_app'));
        shuffle($allowedClientApps);
        $response = $this->postJson('/api/command/uploads/onboarding/drivers',
            [
                'client_app' => array_pop($allowedClientApps),
                'parameters' => [
                    'client_id' => 1,
                    'user_id' => 10831
                ],
                'files' => [
                    [
                        'mime_type' => 'csv',
                        'parameters' => [
                            'vehicle_id' => 25
                        ],
                        'content' => ''
                    ],
                    [
                        'mime_type' => 'csv',
                        'parameters' => [
                            'vehicle_id' => 25
                        ]
                    ],
                    [
                        'mime_type' => 'csv',
                        'parameters' => [
                            'vehicle_id' => 25
                        ],
                        'content' => ''
                    ]
                ]
            ]
        );
        $response->assertUnprocessable();
    }

    public function testNoFilesContentValue(): void
    {
        $allowedClientApps = array_flip(config('allowed_client_app'));
        shuffle($allowedClientApps);
        $response = $this->postJson('/api/command/uploads/onboarding/drivers',
            [
                'client_app' => array_pop($allowedClientApps),
                'parameters' => [
                    'client_id' => 1,
                    'user_id' => 10831
                ],
                'files' => [
                    [
                        'mime_type' => 'csv',
                        'parameters' => [
                            'vehicle_id' => 25
                        ],
                        'content' => 'text'
                    ],
                    [
                        'mime_type' => 'csv',
                        'parameters' => [
                            'vehicle_id' => 25
                        ],
                        'content' => 'data:text/plain;base64,'
                    ],
                    [
                        'mime_type' => 'csv',
                        'parameters' => [
                            'vehicle_id' => 25
                        ],
                        'content' => 'text'
                    ]
                ]
            ]
        );
        $response->assertUnprocessable();
    }
}