Yii 2 JustCoded Files extension
===============================
Yii 2 extension for upload files

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist justcoded/yii2-filestorage "*"
```

or add

```
"justcoded/yii2-filestorage": "*"
```

to the require section of your `composer.json` file.


Apply migrations by following command:
```bash
php yii migrate --migrationPath="@vendor/justcoded/yii2-filestorage/migrations"
```

Add application component to configuration file:
```php
'storage' => [
    'class' => \justcoded\yii2\filestorage\storage\Filestorage::class,
    'adapter' => \justcoded\yii2\filestorage\storage\adapters\LocalAdapter::class,
    'adapterConfig' => [Yii::getAlias('files')]
],
```

Usage
-----

Once the extension is installed, simply use it in your ActiveRecord model by:

```php
// One to Many
public function getFiles()
{
    return $this->morphMany(FlyFile::class, 'fileable', ['attribute' => 'files'], 'fly_file_relation', 'file_id');
}

// One to One
public function getAvatar()
{
    return $this->morphOne(FlyFile::class, 'fileable', ['attribute' => 'avatar'], 'fly_file_relation', 'file_id');
}
```
