<?php

namespace dsj\menu\models;

/**
 * This is the model class for table "menu".
 *
 * @property int $id
 * @property int $pid
 * @property string $title
 * @property int $sort
 * @property string $route
 * @property string $icon
 * @property int $status
 */
class Menu extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid', 'title', 'sort'], 'required'],
            [['pid', 'sort', 'status'], 'integer'],
            [['title'], 'string', 'max' => 50],
            [['route', 'icon','params'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pid' => 'PID',
            'title' => '标题',
            'sort' => '排序',
            'route' => '路由',
            'icon' => '图标',
            'params' => '路由参数'
        ];
    }

    public function getMenuItemById($id){
        return self::findOne(['id' => $id]);
    }

    /**
     * @param $id
     * @return Menu[]
     * 获取所有的孩子
     */
    public function getChildById($id){
        return self::findAll(['pid' => $id]);
    }
}
