<?php

namespace yz\admin\export\backend;
use yii\helpers\ArrayHelper;
use yz\icons\Icons;


/**
 * Class Module
 * @package \yz\admin\export\backend
 */
class Module extends \yz\admin\export\common\Module
{
    public function getAdminMenu()
    {
        return [
            [
                'label' => \Yii::t('admin/export', 'Data export'),
                'icon' => Icons::o('gear'),
                'items' => [
                    [
                        'label' => \Yii::t('admin/export', 'Files'),
                        'icon' => Icons::o('info'),
                        'route' => ['/admin-export/export-requests/index'],
                    ],
                ],
            ],
        ];
    }
} 