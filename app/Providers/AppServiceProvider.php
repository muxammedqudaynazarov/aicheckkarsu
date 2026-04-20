<?php

namespace App\Providers;

use Google\Client;
use Google\Service\Drive;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Storage::extend('google', function ($app, $config) {
            $client = new \Google\Client();
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            $client->refreshToken($config['refreshToken']);

            $service = new \Google\Service\Drive($client);

            $options = [];
            // Faqatgina teamDriveId haqiqatan mavjud bo'lsagina massivga qo'shamiz
            if (!empty($config['teamDriveId'])) {
                $options['teamDriveId'] = $config['teamDriveId'];
            }

            // Agar ID topilmasa, standart asosiy papka 'root' ni tanlaydi ('/' o'rniga)
            $folderId = $config['folder'] ?? 'root';
            if (empty($folderId)) {
                $folderId = 'root';
            }

            $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $folderId, $options);
            $driver = new \League\Flysystem\Filesystem($adapter);

            return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter, $config);
        });
    }
}
