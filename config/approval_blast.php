<?php

/**
 * Approval Blast Configuration
 * 
 * Mendefinisikan daftar divisi penerima email approval untuk setiap tipe dokumen.
 * Setiap divisi akan dipetakan ke user-user dengan role/divisi tersebut.
 */

return [
    /**
     * Token expiration time in days
     */
    'token_expires_in_days' => 7,

    /**
     * Document type to divisions mapping
     * Key: document_type code (dari tabel document_types)
     * Value: array divisi yang harus approve
     */
    'document_divisions' => [
        // Part Submission Warrant
        'PSW' => [
            'purchasing',
        ],

        // Packaging Standard
        'PACKAGING_STANDARD' => [
            'ppc',
            'purchasing',
        ],

        // Inspection Standard - belum ditentukan, kosongkan dulu
        'IS' => [
            // 'qa',
        ],

        // MSA Gage RR - belum ditentukan, kosongkan dulu
        'MSA' => [
            // 'quality',
        ],
    ],

    /**
     * Division to role mapping
     * Mapping antara division name dan role pada tabel users
     * Digunakan untuk query user berdasarkan divisi
     */
    'division_roles' => [
        'purchasing' => 'purchasing',
        'ppc' => 'ppc',
        'qa' => 'qa',
        'quality' => 'quality',
    ],

    /**
     * Signature role labels untuk PDF dan display
     * Memetakan division ke role_type dan role_label untuk table document_signatures
     */
    'signature_role_labels' => [
        'purchasing' => [
            'role_type' => 'purchasing',
            'role_label' => 'Purchasing',
        ],
        'ppc' => [
            'role_type' => 'ppc',
            'role_label' => 'PPC',
        ],
        'qa' => [
            'role_type' => 'qa',
            'role_label' => 'QA',
        ],
        'quality' => [
            'role_type' => 'quality',
            'role_label' => 'Quality',
        ],
    ],

    /**
     * Email notification sender
     */
    'mail_from' => env('MAIL_FROM_ADDRESS', 'noreply@akebono.co.id'),
    'mail_from_name' => env('MAIL_FROM_NAME', 'AAIJ Approval System'),

    /**
     * Retry settings untuk pengiriman email
     */
    'mail_retry_count' => 3,
    'mail_retry_delay' => 60, // detik

    /**
     * Throttle settings untuk route approval
     */
    'throttle_requests' => 20, // requests per minute
];
