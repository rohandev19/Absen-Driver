<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit System Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the comprehensive audit system covering security,
    | performance, database optimization, and code quality analysis.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Enabled Engines
    |--------------------------------------------------------------------------
    |
    | Control which audit engines are enabled. Set to false to skip
    | specific engines during full audit runs.
    |
    */
    'engines' => [
        'security' => env('AUDIT_SECURITY_ENABLED', true),
        'performance' => env('AUDIT_PERFORMANCE_ENABLED', true),
        'database' => env('AUDIT_DATABASE_ENABLED', true),
        'code_quality' => env('AUDIT_CODE_QUALITY_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Output
    |--------------------------------------------------------------------------
    |
    | Default output format and storage path for generated audit reports.
    |
    */
    'report' => [
        'default_format' => env('AUDIT_REPORT_FORMAT', 'html'),
        'output_directory' => storage_path('app/audit-reports'),
        'keep_reports' => env('AUDIT_KEEP_REPORTS', 30), // days to retain
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Scanners
    |--------------------------------------------------------------------------
    |
    | Configuration for individual security scanners.
    |
    */
    'security' => [
        'scanners' => [
            'authentication' => true,
            'authorization' => true,
            'input_validation' => true,
            'csrf_xss' => true,
            'sensitive_data' => true,
            'dependency' => true,
            'configuration' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Analyzers
    |--------------------------------------------------------------------------
    |
    | Configuration for performance analysis thresholds.
    |
    */
    'performance' => [
        'analyzers' => [
            'query' => true,
            'cache' => true,
            'api_response' => true,
            'memory' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Analyzers
    |--------------------------------------------------------------------------
    |
    | Configuration for database audit analysis.
    |
    */
    'database' => [
        'analyzers' => [
            'index' => true,
            'query_optimizer' => true,
            'transaction' => true,
            'connection' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Code Quality Analyzers
    |--------------------------------------------------------------------------
    |
    | Configuration for code quality analysis thresholds.
    |
    */
    'code_quality' => [
        'analyzers' => [
            'style' => true,
            'complexity' => true,
            'test_coverage' => true,
            'documentation' => true,
        ],
        'thresholds' => [
            'max_method_lines' => 50,
            'max_nesting_depth' => 4,
            'max_parameters' => 5,
            'max_controller_methods' => 10,
            'max_file_lines' => 300,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Severity Levels
    |--------------------------------------------------------------------------
    |
    | Define severity weights for prioritization.
    |
    */
    'severity_weights' => [
        'critical' => 5,
        'high' => 4,
        'medium' => 3,
        'low' => 2,
        'info' => 1,
    ],

];
