<?php

return [
    'exports' => [
        /*
        |--------------------------------------------------------------------------
        | Chunk size
        |--------------------------------------------------------------------------
        |
        | When using FromQuery, the query is automatically chunked.
        | Here you can specify how big the chunk should be.
        |
        */
        'chunk_size' => 1000,

        /*
        |--------------------------------------------------------------------------
        | Pre-calculate formulas during export
        |--------------------------------------------------------------------------
        */
        'pre_calculate_formulas' => false,

        /*
        |--------------------------------------------------------------------------
        | Enable strict null comparison
        |--------------------------------------------------------------------------
        |
        | When enabled empty cells ('') will not be converted to NULL after reading.
        |
        */
        'strict_null_comparison' => false,

        /*
        |--------------------------------------------------------------------------
        | CSV Settings
        |--------------------------------------------------------------------------
        |
        | Configure e.g. delimiter, enclosure and line ending for CSV exports.
        |
        */
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'use_bom' => false,
            'include_separator_line' => false,
            'excel_compatibility' => false,
            'output_encoding' => '',
            'test_auto_detect' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | Worksheet properties
        |--------------------------------------------------------------------------
        |
        | Configure e.g. default title, creator, subject...
        |
        */
        'properties' => [
            'creator'        => '',
            'lastModifiedBy' => '',
            'title'          => '',
            'description'    => '',
            'subject'        => '',
            'keywords'       => '',
            'category'       => '',
            'manager'        => '',
            'company'        => '',
        ],
    ],

    'imports' => [
        /*
        |--------------------------------------------------------------------------
        | Read Only
        |--------------------------------------------------------------------------
        |
        | When dealing with imports, you might only be interested in the
        | data that the sheet exists. By default we ignore the formatting,
        | styles, widths, heights, ...
        | But sometimes you might want to still read some of the meta data.
        |
        | You can always override this on a per reader basis.
        |
        */
        'read_only' => true,

        /*
        |--------------------------------------------------------------------------
        | Ignore Empty
        |--------------------------------------------------------------------------
        |
        | When dealing with imports, you might be interested in ignoring
        | rows that have null values or empty strings. By default rows
        | containing empty strings or empty values are not ignored but can be
        | skipped using the ::ignoreEmpty() method.
        |
        */
        'ignore_empty' => false,

        /*
        |--------------------------------------------------------------------------
        | Heading Row Formatter
        |--------------------------------------------------------------------------
        |
        | Configure the heading row formatter.
        | Available options: none|slug|custom
        |
        */
        'heading_row' => [
            'formatter' => 'slug',
        ],

        /*
        |--------------------------------------------------------------------------
        | CSV Settings
        |--------------------------------------------------------------------------
        |
        | Configure e.g. delimiter, enclosure and line ending for CSV imports.
        |
        */
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'escape_character' => '\\',
            'contiguous' => false,
            'input_encoding' => 'UTF-8',
        ],

        /*
        |--------------------------------------------------------------------------
        | Worksheet demarcation
        |--------------------------------------------------------------------------
        |
        | When dealing with imports, you might want to specify
        | which worksheet you are interested in. By default we select the first
        | worksheet by index, but you can also select by name.
        | Here you can specify some worksheet demarcation logic.
        |
        */
        'filter' => 'all',
    ],

    /*
    |--------------------------------------------------------------------------
    | Extension detector
    |--------------------------------------------------------------------------
    |
    | Configure here which writer/reader type should be used when the package
    | needs to guess the correct type based on the extension alone.
    |
    */
    'extension_detector' => [
        'xlsx' => Maatwebsite\Excel\Excel::XLSX,
        'xlsm' => Maatwebsite\Excel\Excel::XLSX,
        'xltx' => Maatwebsite\Excel\Excel::XLSX,
        'xltm' => Maatwebsite\Excel\Excel::XLSX,
        'xls' => Maatwebsite\Excel\Excel::XLS,
        'xlt' => Maatwebsite\Excel\Excel::XLS,
        'ods' => Maatwebsite\Excel\Excel::ODS,
        'ots' => Maatwebsite\Excel\Excel::ODS,
        'slk' => Maatwebsite\Excel\Excel::SLK,
        'xml' => Maatwebsite\Excel\Excel::XML,
        'gnumeric' => Maatwebsite\Excel\Excel::GNUMERIC,
        'htm' => Maatwebsite\Excel\Excel::HTML,
        'html' => Maatwebsite\Excel\Excel::HTML,
        'csv' => Maatwebsite\Excel\Excel::CSV,
        'tsv' => Maatwebsite\Excel\Excel::TSV,

        /*
        |--------------------------------------------------------------------------
        | PDF Extension
        |--------------------------------------------------------------------------
        |
        | Configure here which Pdf driver should be used by default.
        | Available options: Excel::MPDF | Excel::TCPDF | Excel::DOMPDF
        |
        */
        'pdf' => Maatwebsite\Excel\Excel::DOMPDF,
    ],

    /*
    |--------------------------------------------------------------------------
    | Value Binder
    |--------------------------------------------------------------------------
    |
    | PhpSpreadsheet offers a way to hook into the process of a value being
    | written to a cell. In there some assumptions are made on how the
    | value should be formatted. If you want to change those defaults,
    | you can implement your own default value binder.
    |
    | Possible value binders:
    |
    | [x] Maatwebsite\Excel\DefaultValueBinder::class
    | [x] PhpOffice\PhpSpreadsheet\Cell\StringValueBinder::class
    | [x] PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder::class
    |
    */
    'value_binder' => [
        'default' => Maatwebsite\Excel\DefaultValueBinder::class,
    ],

    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Default cell caching driver
        |--------------------------------------------------------------------------
        |
        | By default PhpSpreadsheet keeps all cell values in memory, however when
        | dealing with large files, this might result into memory issues. If you
        | want to mitigate that, you can configure a cell caching driver here.
        | When using the illuminate driver, it will store each value in the
        | cache store. This can slow down the process, but reduce memory usage.
        |
        */
        'driver' => 'memory',

        /*
        |--------------------------------------------------------------------------
        | Cache store
        |--------------------------------------------------------------------------
        |
        | Used for the illuminate cache driver
        |
        */
        'store' => null,

        /*
        |--------------------------------------------------------------------------
        | Illuminate cache settings
        |--------------------------------------------------------------------------
        |
        | When using the illuminate cache driver, we can define the ttl of values
        | that are stored in the cache store, and the cache tag that should be
        | assigned to the values store in the cache.
        |
        */
        'illuminate' => [
            'store' => null,
            'ttl' => 600,
            'tag' => 'laravel-excel',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Handler
    |--------------------------------------------------------------------------
    |
    | By default the import is wrapped in a transaction. This is useful
    | for when an import may fail and you want to retry it. By using
    | transactions, the previous import gets rolled-back.
    |
    | You can disable the transaction handler by setting this to null.
    | Or you can choose a custom made transaction handler here.
    |
    | Supported handlers: null|db
    |
    */
    'transactions' => [
        'handler' => 'db',
        'db' => [
            'connection' => null,
        ],
    ],

    'temporary_files' => [
        'local_path' => storage_path('app'),
        'remote_disk' => null,
        'remote_prefix' => null,
        'force_resync_remote' => null,
    ],
];