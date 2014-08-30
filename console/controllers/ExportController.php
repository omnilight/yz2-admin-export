<?php

namespace yz\admin\export\console\controllers;

use console\base\Controller;
use Yii;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\mutex\FileMutex;
use yz\admin\export\common\actions\ExportAction;
use yz\admin\export\common\models\ExportRequest;
use yz\admin\models\SystemEvent;
use yz\admin\widgets\GridView;


/**
 * Class ExportController
 * @package \yz\admin\export\console\controllers
 */
class ExportController extends Controller
{
    const LOCK_NAME = 'export_export_process';

    public function actionProcess()
    {
        $mutex = new FileMutex();
        if ($mutex->acquire(self::LOCK_NAME) == false) {
            Console::output("Another process is already running, exit");
            return self::EXIT_CODE_NORMAL;
        }

        /** @var ActiveQuery $exportRequests */
        $requestsQuery = ExportRequest::find()
            ->where(['is_exported' => 0])->orderBy(['created_at' => SORT_ASC]);

        try {
            foreach ($requestsQuery->each() as $exportRequest) {
                /** @var ExportRequest $exportRequest */
                $this->processExportRequest($exportRequest);
            }
        } catch (\Exception $e) {
            $mutex->release(self::LOCK_NAME);
            throw $e;
        }

        $mutex->release(self::LOCK_NAME);

        return self::EXIT_CODE_NORMAL;
    }

    /**
     * @param ExportRequest $request
     * @return bool
     */
    protected function processExportRequest($request)
    {
        $controllerClassName = $request->data['controllerClassName'];
        $actionId = $request->data['actionId'];
        $requestParams = $request->data['params'];
        /** @var \backend\base\Controller $originalController */
        $originalController = new $controllerClassName('temp-controller', $this->module);
        /** @var ExportAction $action */
        $action = $originalController->createAction($actionId);
        if (!(is_a($action, ExportAction::className())))
            return false;

        /** @var DataProviderInterface $dataProvider */
        $dataProvider = call_user_func($action->dataProvider, $requestParams);
        /** @var array $gridColumns */
        $gridColumns = call_user_func($action->getGridColumns());

        $grid = GridView::widget([
            'renderAllPages' => true,
            'runInConsoleMode' => true,
            'layout' => "{items}",
            'tableOptions' => ['class' => ''],
            'dataProvider' => $dataProvider,
            'columns' => $gridColumns,
        ]);

        $fileContent = strtr(ExportAction::EXPORT_TEMPLATE, [
            '{name}' => $action->reportName,
            '{grid}' => $grid,
        ]);

        $fileName = 'Export_' . $request->id . '_' . strtotime('%d%m%Y%H%i%s') . '.xls';
        $request->file = $fileName;
        $request->is_exported = 1;
        $request->save();

        FileHelper::createDirectory(Yii::getAlias(ExportRequest::FILE_PATH));
        file_put_contents(Yii::getAlias($request->fullFileName), $fileContent);

        SystemEvent::create('success', $request->user_id, Yii::t('admin/export','Data requested for exporting is ready'), ['/admin-export/export-requests/index']);

        return true;
    }
} 