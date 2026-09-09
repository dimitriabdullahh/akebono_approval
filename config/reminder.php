<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Document Submission Reminder Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration untuk reminder dan escalation ketika supplier belum
    | mengirimkan dokumen sama sekali.
    |
    | TESTING MODE: 1 minute initial, 1 minute escalation
    | PRODUCTION MODE: Ubah ke 1440 (1 day) atau sesuai kebutuhan bisnis
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Initial Reminder Delay (dalam menit)
    |--------------------------------------------------------------------------
    |
    | Waktu tunggu setelah request dibuat sebelum mengirim reminder pertama
    | ke PIC Supplier.
    |
    | TESTING: 1
    | PRODUCTION: 1440 (1 day)
    |
    */
    'initial_reminder_delay' => env('REMINDER_INITIAL_DELAY', 0),

    /*
    |--------------------------------------------------------------------------
    | Escalation Delay (dalam menit)
    |--------------------------------------------------------------------------
    |
    | Waktu tunggu antara setiap level escalation.
    |
    | TESTING: 1
    | PRODUCTION: 1440 (1 day)
    |
    */
    'escalation_delay' => env('REMINDER_ESCALATION_DELAY', 1),

    /*
    |--------------------------------------------------------------------------
    | Supplier Escalation Levels
    |--------------------------------------------------------------------------
    |
    | Level escalation untuk supplier berdasarkan hierarki organisasi.
    | Level 1: PIC Supplier (pic_email dari supplier table)
    | Level 2: Supervisor (harus ada di supplier)
    | Level 3: Head Department (harus ada di supplier)
    | Level 4: General Manager (harus ada di supplier)
    | Level 5: Direktur Utama (harus ada di supplier)
    |
    */
    'escalation_levels' => [
        1 => 'PIC',
        2 => 'Supervisor',
        3 => 'Head Department',
        4 => 'General Manager',
        5 => 'Director',
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Recipients per Level
    |--------------------------------------------------------------------------
    |
    | Mapping level ke field email di supplier atau default emails
    |
    */
    'recipient_mapping' => [
        1 => 'pic_email',           // Field di suppliers table
        2 => 'supervisor_email',    // Field di suppliers table (akan ditambahkan)
        3 => 'head_dept_email',     // Field di suppliers table (akan ditambahkan)
        4 => 'gm_email',            // Field di suppliers table (akan ditambahkan)
        5 => 'director_email',      // Field di suppliers table (akan ditambahkan)
    ],
];
