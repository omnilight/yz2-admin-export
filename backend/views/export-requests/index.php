<?php

use yii\helpers\Html;
use yz\admin\widgets\Box;
use yz\admin\widgets\GridView;
use yz\admin\widgets\ActionButtons;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $columns
 */

$this->title = Yii::t('admin/export','Files');
$this->params['breadcrumbs'][] = $this->title;
$this->params['header'] = $this->title;
?>
<?php $box = Box::begin(['cssClass' => 'export-request-index box-primary']) ?>
    <div class="text-right">
        <?php echo ActionButtons::widget([
            'order' => [['delete']],
            'gridId' => 'export-request-grid',
            'modelClass' => 'yz\admin\export\common\models\ExportRequest',
        ]) ?>
    </div>


    <?= GridView::widget([
        'id' => 'export-request-grid',
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => array_merge([
            ['class' => 'yii\grid\CheckboxColumn'],
        ], $columns, [
            [
                'class' => 'yz\admin\widgets\ActionColumn',
                'template' => '{download} {delete}',
                'buttons' => [
                    'download' => function ($url, $model, $key) {
                            return Html::a(\yz\icons\Icons::i('download'), ['download', 'id' => $key], [
                                'class' => 'btn btn-primary',
                                'title' => Yii::t('admin/export','Download')
                            ]);
                        }
                ]
            ],
        ]),
    ]); ?>
<?php Box::end() ?>
