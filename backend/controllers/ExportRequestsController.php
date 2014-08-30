<?php

namespace yz\admin\export\backend\controllers;

use Yii;
use yz\admin\export\common\models\ExportRequest;
use yii\data\ActiveDataProvider;
use backend\base\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yz\admin\actions\ExportAction;
use yz\admin\widgets\ActiveForm;

/**
 * ExportRequestsController implements the CRUD actions for ExportRequest model.
 */
class ExportRequestsController extends Controller
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * Lists all ExportRequest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ExportRequest::find()->where(['is_exported' => 1, 'user_id' => Yii::$app->user->id]),
            'sort' => [
                'defaultOrder' => ['exported_at' => SORT_DESC],
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'columns' => $this->getGridColumns(),
        ]);
    }

    public function getGridColumns()
    {
        return [
			'id',
			'file',
			'created_at:datetime',
            'exported_at:datetime',
            // 'is_exported:boolean',
            // 'file',
        ];
    }

    public function actionDownload($id)
    {
        $model = $this->findModel($id);

        return Yii::$app->response->sendFile(Yii::getAlias($model->fullFileName), $model->file, 'application/msexcel');
    }


    /**
     * Deletes an existing ExportRequest model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if (is_array($id)) {
            $message = \Yii::t('admin/t', 'Records were successfully deleted');
        } else {
            $id = (array)$id;
            $message = \Yii::t('admin/t', 'Record was successfully deleted');
        }

        foreach ($id as $id_)
            $this->findModel($id_)->delete();

        \Yii::$app->session->setFlash(\yz\Yz::FLASH_SUCCESS, $message);

        return $this->redirect(['index']);
    }

    /**
     * Finds the ExportRequest model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ExportRequest the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ExportRequest::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
