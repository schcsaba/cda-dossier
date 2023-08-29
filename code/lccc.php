<?php

use App\Definitions\UploadConfigurationParameters;
use App\Definitions\ValueType;
use App\UploadSteps\CastValues;
use App\UploadSteps\FuelTransaction\CreateFuelTransactionImportHistory;
use App\UploadSteps\GenerateUnitPriceExclVat;
use App\UploadSteps\GenerateVatRate;
use App\Definitions\FuelTransactionMap;
use App\UploadSteps\CheckGlobalParameters;
use App\UploadSteps\CheckRequiredHeaders;
use App\UploadSteps\CleanData;
use App\UploadSteps\ConvertEncoding;
use App\UploadSteps\GenerateLocationName;
use App\UploadSteps\ExtractToArray;
use App\UploadSteps\FuelTransaction\BindValues;
use App\UploadSteps\FuelTransaction\ExportTransactionsInMicroservice;
use App\UploadSteps\FuelTransaction\FilterAndMapFuelTransactions;
use App\UploadSteps\FuelTransaction\GetFuelSupplierId;
use App\UploadSteps\FuelTransaction\GetMemberCode;
use App\UploadSteps\GenerateInvoiceNumber;
use App\UploadSteps\GetVehicle;

return [
    'process' => [
        [
            'class_namespace' => GetFuelSupplierId::class
        ],
        [
            'class_namespace' => CreateFuelTransactionImportHistory::class
        ],
        [
            'class_namespace' => CheckGlobalParameters::class,
            'parameters' => [
                'client_id',
                'user_id'
            ]
        ],
        [
            'class_namespace' => ConvertEncoding::class,
            'parameters' => [
                'from' => 'ISO-8859-15',
                'to' => 'UTF-8'
            ]
        ],
        [
            'class_namespace' => ExtractToArray::class,
            'parameters' => [
                'end_of_line_char' => "\n"
            ]
        ],
        [
            'class_namespace' => CleanData::class,
            'parameters' => [
                'separator' => ';',
                'chars_to_remove' => "'"
            ]
        ],
        [
            'class_namespace' => CheckRequiredHeaders::class,
            'parameters' => [
                'separator' => ';',
                'headers' => "Enseigne;Site : Code site;Site : Numéro de terminal;Site : Libellé;Site : Libellé court;Site : Type du site;Client : Référence client;Client : Nom du client;Carte : Type de carte;Carte : Numéro de carte;Carte : Date de validité;Numéro de TLC;Date de télécollecte;Type de transaction;Numéro de transaction;Date de transaction;Code devise;Code produit;Produit;Prix unitaire;Quantité;Montant;Montant HT;Code véhicule;Code chauffeur;Kilométrage;Immatriculation;Code réponse;Numéro d'opposition;Numéro d'autorisation;Motif d'autorisation;Mode de transaction;Mode de vente;Mode de validation;Facturation client;Facturation site",
            ]
        ],
        [
            'class_namespace' => GetMemberCode::class,
            'parameters' => [
                'separator' => ';',
                'header' => 'Client : Référence client'
            ]
        ],
        [
            'class_namespace' => GenerateVatRate::class,
            'parameters' => [
                'separator' => ';',
                'price_with_taxes_field' => 'Montant',
                'price_without_taxes_field' => 'Montant HT',
                'field_names' => ['vat_rate']
            ]
        ],
        [
            'class_namespace' => GenerateUnitPriceExclVat::class,
            'parameters' => [
                'separator' => ';',
                'quantity_field' => 'Quantité',
                'price_without_taxes_field' => 'Montant HT',
                'field_names' => ['up_excl_vat']
            ]
        ],
        [
            'class_namespace' => GenerateInvoiceNumber::class,
            'parameters' => [
                'separator' => ';',
                'client_reference_field' => 'Client : Référence client',
                'field_names' => ['invoice_number']
            ]
        ],
        [
            'class_namespace' => GenerateLocationName::class,
            'parameters' => [
                'separator' => ';',
                'location_name_field' => 'Site : Libellé',
                'field_names' => ['location_name']
            ]
        ],
        [
            'class_namespace' => FilterAndMapFuelTransactions::class,
            'parameters' => [
                'delimiter' => ';',
                'filter' => [
                    'invoice_number',
                    'invoice_date',
                    'Client : Référence client',
                    'Date de transaction',
                    'Numéro de transaction',
                    'Montant HT',
                    'Quantité',
                    'vat_rate',
                    'up_excl_vat',
                    'Code produit',
                    'Immatriculation',
                    'Kilométrage',
                    'Produit',
                    'Code produit',
                    'Site : Libellé',
                    'location_name',
                    'Carte : Numéro de carte'

                ],
                'mapping' => [
                    FuelTransactionMap::INVOICE_DATE => 'invoice_date',
                    FuelTransactionMap::INVOICE_NUMBER => 'invoice_number',
                    FuelTransactionMap::MEMBER_CODE => 'Client : Référence client',
                    FuelTransactionMap::TRANSACTION_NUMBER => 'Numéro de transaction',
                    FuelTransactionMap::TRANSACTION_DT => 'Date de transaction',
                    FuelTransactionMap::TRANSACTION_AMOUNT_EXCL_VAT => 'Montant HT',
                    FuelTransactionMap::QUANTITY => 'Quantité',
                    FuelTransactionMap::VAT_RATE => 'vat_rate',
                    FuelTransactionMap::UP_EXCL_VAT => 'up_excl_vat',
                    FuelTransactionMap::REGPLATE => 'Immatriculation',
                    FuelTransactionMap::PRODUCT_CODE => 'Code produit',
                    FuelTransactionMap::PRODUCT_NAME => 'Produit',
                    FuelTransactionMap::TRANSACTION_TYPE_CODE => 'Code produit',
                    FuelTransactionMap::TRANSACTION_TYPE_NAME => 'Produit',
                    FuelTransactionMap::LOCATION_NAME => 'location_name',
                    FuelTransactionMap::CITY => 'location_name',
                    FuelTransactionMap::SUBSCRIPTION_CARD_NUMBER => 'Carte : Numéro de carte',
                    FuelTransactionMap::VEHICLE_ODOMETER_IN_KM => 'Kilométrage',
                    FuelTransactionMap::VAT_AMOUNT => null
                ]
            ]
        ],
        [
            'class_namespace' => CastValues::class,
            'parameters' =>
                [
                    [
                        'keys' => [
                            FuelTransactionMap::TRANSACTION_AMOUNT_EXCL_VAT,
                            FuelTransactionMap::UP_EXCL_VAT,
                            FuelTransactionMap::QUANTITY,
                            FuelTransactionMap::VAT_RATE,
                        ],
                        'type' => ValueType::FLOAT,
                        'format' => ['from' => ',', 'to' => '.'],
                    ],
                    [
                        'keys' => [FuelTransactionMap::VEHICLE_ODOMETER_IN_KM],
                        'type' => ValueType::INTEGER
                    ],
                    [
                        'keys' => [FuelTransactionMap::INVOICE_DATE],
                        'type' => ValueType::DATE,
                        'format' => ['from' => null]
                    ],
                    [
                        'keys' => [FuelTransactionMap::TRANSACTION_DT],
                        'type' => ValueType::DATETIME,
                        'format' => ['from' => 'd-m-Y H:i:s']
                    ]
                ]
        ],
        [
            'class_namespace' => GetVehicle::class
        ],

        [
            'class_namespace' => BindValues::class,
            'parameters' => [
                UploadConfigurationParameters::WITH_SUBSCRIPTION_CARDS => true,
            ]
        ],
        [
            'class_namespace' => ExportTransactionsInMicroservice::class,
            'parameters' => []
        ]
    ],
    'error' => [
        [
            'class_namespace' => \App\UploadErrorTasks\FuelTransaction\UpdateFuelTransactionImportHistory::class,
        ]
    ]
];