<?php

namespace yz\admin\export\common\actions;
use yii\base\Action;
use Yii;
use yii\data\DataProviderInterface;
use yii\helpers\Json;
use yii\web\Controller;
use yz\admin\export\common\models\ExportRequest;
use yz\admin\widgets\GridView;
use yz\Yz;


/**
 * Class ExportAction
 * @package \yz\admin\export\common\actions
 */
class ExportAction extends \yz\admin\actions\ExportAction
{
    public function run()
    {
        /** @var Controller $controller */
        $controller = $this->controller;
        $data = [
            'controllerClassName' => $controller->className(),
            'actionId' => $this->id,
            'params' => Yii::$app->request->getQueryParams(),
        ];

        $exportRequest = new ExportRequest();
        $exportRequest->user_id = Yii::$app->user->id;
        $exportRequest->data = $data;
        if ($exportRequest->save() == false)
            Yz::errorFlash(Json::encode($exportRequest->getFirstErrors()));

        Yii::$app->session->setFlash(Yz::FLASH_INFO, Yii::t('admin/export','Export request was added to queue. System will notify you when it will be done'));

        return $controller->goBack();
    }

} 