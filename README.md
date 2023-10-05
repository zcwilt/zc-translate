# Installation

Clone the repository and run the following command in the root directory:

composer install


## Usage

Run the following command in the root directory:

php artisan translate:all {source-language} {target-language}

note:source-language and target-language are ISO-2 codes, e.g en fr de es etc.

e.g php artisan translate:all en fr

you can also add a source directory prefix, e.g to target an admin directory or a plugin directory.

Converted files are stored to the laravel storage/app directory.

@todo how to rebuild (e.g. clear cache and output files)

## Configuration

`config/translate.php`

This allows for overriding the result of a possible language conversion. 
e.g. if the default conversion by Google is incorrect, you can add a custom conversion here.

@todo explain the format of the config file.
