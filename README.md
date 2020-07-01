# ImageServer Plugin for SW5

## INSTALLATION & CONFIGURATION
1. Clone the plugin sourcecode from: https://github.com/SHOPMACHER/image-server-sw5
2. Install the plugin in the Plugin Manager or using this command
    ```shell script
    $ ./bin/console sw:plugin:install SmImageServer --clear-cache
    ```
3. Update the plugin configuration for the ImageServer API authentication.
4. Update the Shopware CDN configuration .In ./config.php, replace/edit your configuration like this:
    ```
    # config.php
        'cdn' => [
            'backend'  => 'ImageServer',
            'strategy' => 'ImageServer',
            'adapters' => [
                'local'       => [
                    'type'     => 'local',
                    'mediaUrl' => 'md5',
                    'path'     => realpath(__DIR__ . '/')
                ],
                'ImageServer' => [
                    'type'     => 'ImageServer',
                    'strategy' => 'ImageServer',
                    'mediaUrl' => 'https://imageserver.scalecommerce.cloud/images/[YOUR_IMAGESERVER_PROJECT_NAME]/'
                ]
            ]
        ],
    ```

## Migration
Command to migrate images will be coming soon.
```

```


