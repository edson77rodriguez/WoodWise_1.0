
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | SIGMAD mantiene la conexión database como predeterminada.
    | Los procesos geoespaciales pesados utilizan database_mosaic.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        /*
        |--------------------------------------------------------------------------
        | Synchronous Queue
        |--------------------------------------------------------------------------
        */

        'sync' => [
            'driver' => 'sync',
        ],

        /*
        |--------------------------------------------------------------------------
        | Default Database Queue
        |--------------------------------------------------------------------------
        |
        | Mantiene la configuración original para tareas normales.
        |
        */

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | SIGMAD - Heavy Mosaic Processing Queue
        |--------------------------------------------------------------------------
        |
        | Conexión dedicada para:
        |
        | - Inspección de ortomosaicos GeoTIFF.
        | - Verificación de metadatos geoespaciales.
        | - Validación de integridad del archivo.
        | - Generación y verificación de COG.
        |
        | La conexión utiliza la misma tabla jobs, pero una cola
        | independiente denominada mosaic.
        |
        | IMPORTANTE:
        | El timeout efectivo del trabajador y de cada Job debe ser
        | menor que retry_after, para evitar ejecuciones duplicadas.
        |
        */

        'database_mosaic' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_MOSAIC_QUEUE', 'mosaic'),
            'retry_after' => (int) env('DB_MOSAIC_RETRY_AFTER', 2400),
            'after_commit' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | Beanstalkd Queue
        |--------------------------------------------------------------------------
        */

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => (int) env('BEANSTALKD_QUEUE_RETRY_AFTER', 90),
            'block_for' => 0,
            'after_commit' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Amazon SQS Queue
        |--------------------------------------------------------------------------
        */

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env(
                'SQS_PREFIX',
                'https://sqs.us-east-1.amazonaws.com/your-account-id'
            ),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Redis Queue
        |--------------------------------------------------------------------------
        */

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
            'block_for' => null,
            'after_commit' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'failed_jobs',
    ],

];
