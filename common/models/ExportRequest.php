<?php

namespace yz\admin\export\common\models;

use Yii;
use yii\helpers\Json;
use yz\admin\models\User;
use yz\interfaces\ModelInfoInterface;

/**
 * This is the model class for table "ms_smart_admin_export_requests".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $data_raw
 * @property string $created_at
 * @property string $updated_at
 * @property string $file
 * @property integer $is_exported
 * @property string $exported_at
 *
 * @property User $user
 */
class ExportRequest extends \yz\db\ActiveRecord implements ModelInfoInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_export_requests}}';
    }

	/**
     * Returns model title, ex.: 'Person', 'Book'
     * @return string
     */
    public static function modelTitle()
    {
        return Yii::t('admin/export', 'Export Request');
    }

    /**
     * Returns plural form of the model title, ex.: 'Persons', 'Books'
     * @return string
     */
    public static function modelTitlePlural()
    {
        return Yii::t('admin/export', 'Export Requests');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'is_exported'], 'integer'],
            [['created_at', 'updated_at', 'exported_at'], 'safe'],
            [['data_raw', 'file'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('admin/export', 'ID'),
            'user_id' => Yii::t('admin/export', 'User ID'),
            'data_raw' => Yii::t('admin/export', 'Data Raw'),
            'data' => Yii::t('admin/export', 'Data'),
            'created_at' => Yii::t('admin/export', 'Created At'),
            'updated_at' => Yii::t('admin/export', 'Updated At'),
            'file' => Yii::t('admin/export', 'File'),
            'is_exported' => Yii::t('admin/export', 'Is Exported'),
            'exported_at' => Yii::t('admin/export', 'Exported At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return Json::decode($this->data_raw);
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data_raw = Json::encode($data);
    }
}