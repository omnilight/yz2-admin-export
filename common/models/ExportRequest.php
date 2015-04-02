<?php

namespace yz\admin\export\common\models;

use Yii;
use yii\base\ModelEvent;
use yii\behaviors\TimestampBehavior;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
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
 * @property array $data
 * @property string $fullFileName Returns path alias for the file
 *
 * @property User $user
 */
class ExportRequest extends \yz\db\ActiveRecord implements ModelInfoInterface
{
    const FILE_PATH = '@backend/runtime/admin-export';

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

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('NOW()'),
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'is_exported'], 'integer'],
            [['created_at', 'updated_at', 'exported_at'], 'safe'],
            [['file'], 'string', 'max' => 255],
            ['data_raw', 'string'],
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

    public function beforeSave($insert)
    {
        if ($this->is_exported == 1 && $this->isAttributeChanged('is_exported')) {
            $this->exported_at = new Expression('NOW()');
        }
        return parent::beforeSave($insert);
    }

    public function afterDelete()
    {
        @unlink(Yii::getAlias($this->fullFileName));
        parent::afterDelete();
    }


    public function getFullFileName()
    {
        return self::FILE_PATH . '/' . $this->file;
    }
}
