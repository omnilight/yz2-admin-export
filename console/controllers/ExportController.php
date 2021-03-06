<?php

namespace yz\admin\export\console\controllers;

use console\base\Controller;
use Yii;
use yii\base\Event;
use yii\base\Exception;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\mutex\FileMutex;
use yz\admin\export\common\actions\ExportAction;
use yz\admin\export\common\models\ExportRequest;
use yz\admin\grid\GridView;
use yz\admin\models\SystemEvent;


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
                echo "[#".$exportRequest->id.": ".$exportRequest->created_at."] - ...";
                $this->processExportRequest($exportRequest);
                $memoryPeak = round(memory_get_peak_usage() / 1024 / 1024,2);
                $memory = round(memory_get_usage() / 1024 / 1024,2);
                echo "\r[#".$exportRequest->id.": ".$exportRequest->created_at."] [Mem: {$memoryPeak}Mb/{$memory}Mb] - Success!\n";
            }
        } catch (\Exception $e) {
            $mutex->release(self::LOCK_NAME);
            throw $e;
        }

        $mutex->release(self::LOCK_NAME);

        return self::EXIT_CODE_NORMAL;
    }

    public function actionList()
    {
        Console::output("Currently waiting exports:");

        /** @var ActiveQuery $exportRequests */
        $requestsQuery = ExportRequest::find()
            ->where(['is_exported' => 0])->orderBy(['created_at' => SORT_ASC]);

        foreach ($requestsQuery->each() as $exportRequest) {
            /** @var ExportRequest $exportRequest */
            Console::output("[#".$exportRequest->id.": ".$exportRequest->created_at."] - ".$exportRequest->data_raw);
        }
    }

    public function actionClear($id = null)
    {
        $params = [
            'is_exported' => 0,
        ];
        if ($id !== null) {
            $params['id'] = $id;
        }
        ExportRequest::deleteAll($params);
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

        if (is_callable($action->searchModel)) {
            $searchModel = call_user_func($action->searchModel, $requestParams);
        } else {
            $searchModel = $action->searchModel;
        }
        /** @var DataProviderInterface $dataProvider */
        $dataProvider = call_user_func($action->dataProvider, $requestParams, $searchModel);
        /** @var array $gridColumns */
        $gridColumns = call_user_func($action->getGridColumns(), $searchModel, $dataProvider, $requestParams);

        try {
            $exportedContent = GridView::widget([
                'renderAllPages' => true,
                'runInConsoleMode' => true,
                'layout' => "{items}",
                'tableOptions' => ['class' => ''],
                'dataProvider' => $dataProvider,
                'columns' => $gridColumns,
            ]);
        } catch (Exception $e) {

            $request->delete();
            SystemEvent::create('error', $request->user_id, Yii::t('admin/export','Requested data was not exported, aborting'));

            return false;
        }

        $fileContent = strtr(ExportAction::EXPORT_TEMPLATE, [
            '{name}' => $action->reportName,
            '{grid}' => $exportedContent,
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