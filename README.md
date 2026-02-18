# Papertrail

A Laravel Nova tool for uploading PDFs, extracting metadata and adding placeholders to form input fields.

# Warning!
**It is currently being developed for a specific project and not yet ready for general use.**

## TODO
- [ ] Add tests
- [ ] Implement configuration for controlling the deletion of uploaded PDFs
- [ ] Auto-update page-display in the tool page after processing
- [ ] Add UI-elements for showing a summary of the extracted form fields

## Installation
Install the package via composer:   
`composer require fanmade/nova-papertrail`

Run the publish command:  
 `php artisan  vendor:publish --provider="Fanmade\Papertrail\PapertrailServiceProvider"`  

If you only want to publish the config file:  
`php artisan vendor:publish --tag=papertrail-config`
or the migrations:  
`php artisan vendor:publish --tag=papertrail-migrations`

**Important**:  
If you want to have the PDFs stored in a tenant-context, you need to move the migrations to your tenant-specific migrations directory before running the migrations.

