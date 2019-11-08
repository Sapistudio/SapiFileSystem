# SapiFileSystem
## Get all files in folder
```php
use \SapiStudio\FileSystem\Handler as FileHandler;

$files = FileHandler::getFinder()->files()->in('localpath');
```
\SapiStudio\FileSystem\Handler::get($queryFile)
## Getdata from file
```php
\SapiStudio\FileSystem\Handler::get($filename)
```

## Get a json file data
```php
\SapiStudio\FileSystem\Handler::loadJson($filePath,true(if you want your data as array))
```
## Dump data to file
```php
\SapiStudio\FileSystem\Handler::dumpFile($location,$text);
```

## Dump data as json
```php
\SapiStudio\FileSystem\Handler::dumpJson($location,$array);
```
