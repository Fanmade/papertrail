# Papertrail

A Laravel Nova tool for uploading PDFs, extracting metadata and adding placeholders to form input fields.

## Installation
Install the package via composer:   
`composer require vqs-soltions/nova-papertrail`

Run the publish command:  
 `php artisan  vendor:publish --provider="Vqs\Papertrail\PapertrailServiceProvider"`  

If you only want to publish the config file:  
`php artisan vendor:publish --tag=papertrail-config`
or the migrations:  
`php artisan vendor:publish --tag=papertrail-migrations`

**Important**:  
If you want to have the PDFs stored in a tenant-context, you need to move the migrations to your tenant-context before running the migrations.

