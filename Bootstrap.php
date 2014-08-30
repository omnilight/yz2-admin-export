<?php

namespace yz\admin\export;
use yii\base\Application;
use yii\base\BootstrapInterface;


/**
 * Class Bootstrap
 * @package \yz\admin\export
 */
class Bootstrap implements BootstrapInterface
{

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->i18n->translations['admin/export'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => '@yz/admin/export/common/messages',
            'sourceLanguage' => 'en-US',
        ];
    }
}