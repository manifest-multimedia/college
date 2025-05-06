<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Class Namespace
    |--------------------------------------------------------------------------
    |
    | This value sets the root namespace for Livewire component classes in
    | your application. This allows you to organize your components in a
    | custom namespace.
    |
    */

    'class_namespace' => 'App\\Livewire',

    /*
    |--------------------------------------------------------------------------
    | View Path
    |--------------------------------------------------------------------------
    |
    | This value sets the path for Livewire component views. This allows you
    | to organize your component views in a custom location.
    |
    */

    'view_path' => resource_path('views/livewire'),

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | The default layout to be used when rendering Livewire components.
    |
    */

    'layout' => 'layouts.app',

    /*
    |--------------------------------------------------------------------------
    | Temporary File Uploads
    |--------------------------------------------------------------------------
    |
    | Configuration for temporary file uploads, including storage, previewable
    | MIME types, and validation rules.
    |
    */

    'temporary_file_upload' => [
        /*
        |----------------------------------------------------------------------
        | Storage Disk
        |----------------------------------------------------------------------
        |
        | The storage disk to use for temporary file uploads. Ensure this disk
        | is writable in production (e.g., storage/app/livewire-tmp).
        |
        */
        'disk' => 'local',

        /*
        |----------------------------------------------------------------------
        | Directory
        |----------------------------------------------------------------------
        |
        | The directory within the disk to store temporary files.
        |
        */
        'directory' => 'livewire-tmp',

        /*
        |----------------------------------------------------------------------
        | Preview MIME Types
        |----------------------------------------------------------------------
        |
        | The MIME types that can be previewed using temporaryUrl(). Expanded to
        | include common file types to prevent FileNotPreviewableException.
        |
        */
        'preview_mimes' => [
            'png',
            'gif',
            'bmp',
            'svg',
            'jpg',
            'jpeg',
            'tiff',
            'webp',
            'pdf',
            'doc',
            'docx',
            'txt',
            'mp3',
            'wav',
            'ogg',
            'm4a',
            'mp4',
            'mov',
            'mkv',
            'webm',
            'flv',
        ],

        /*
        |----------------------------------------------------------------------
        | Rules
        |----------------------------------------------------------------------
        |
        | Validation rules applied to temporary file uploads. Adjust as needed
        | for your application.
        |
        */
        'rules' => [
            'file',
            'mimes:png,gif,bmp,svg,jpg,jpeg,tiff,webp,pdf,doc,docx,txt,mp3,wav,ogg,m4a,mp4,mov,mkv,webm,flv',
            'max:10240', // 10MB max file size
        ],

        /*
        |----------------------------------------------------------------------
        | Middleware
        |----------------------------------------------------------------------
        |
        | Middleware applied to temporary file upload routes.
        |
        */
        'middleware' => ['web'],

        /*
        |----------------------------------------------------------------------
        | Max Upload Time
        |----------------------------------------------------------------------
        |
        | The maximum time (in seconds) a temporary file can be stored before
        | it is deleted.
        |
        */
        'max_upload_time' => 5 * 60, // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Render On Redirect
    |--------------------------------------------------------------------------
    |
    | Whether Livewire should render the component on redirect responses.
    |
    */

    'render_on_redirect' => false,

    /*
    |--------------------------------------------------------------------------
    | Back Button Cache
    |--------------------------------------------------------------------------
    |
    | Whether to disable the back button cache for Livewire requests.
    |
    */

    'disable_back_button_cache' => true,

    /*
    |-------------------------------------------------------------------------- 
    | Asset Injection
    |-------------------------------------------------------------------------- 
    |
    | Whether to automatically inject Livewire's JavaScript and CSS assets.
    |
    */

    'inject_assets' => true,

    /*
    |-------------------------------------------------------------------------- 
    | Asset URLs
    |-------------------------------------------------------------------------- 
    |
    | Custom URLs for Livewire assets if you want to serve them from a CDN.
    |
    */

    'asset_url' => null,
    'script_url' => null,

    /*
    |-------------------------------------------------------------------------- 
    | Pagination
    |-------------------------------------------------------------------------- 
    |
    | The default pagination theme for Livewire components.
    |
    */

    'pagination_theme' => 'tailwind',

    /*
    |-------------------------------------------------------------------------- 
    | Legacy Model Binding
    |-------------------------------------------------------------------------- 
    |
    | Whether to enable legacy model binding behavior (Livewire v2 style).
    |
    */

    'legacy_model_binding' => false,

    /*
    |-------------------------------------------------------------------------- 
    | Query String
    |-------------------------------------------------------------------------- 
    |
    | Whether Livewire should automatically update the query string.
    |
    */

    'query_string' => true,

    /*
    |-------------------------------------------------------------------------- 
    | Testing
    |-------------------------------------------------------------------------- 
    |
    | Configuration for Livewire's testing features.
    |
    */

    'testing' => [
        'ensure_pages_exist' => true,
    ],

];