<?php

use dsj\components\helpers\Html;
use jianyan\treegrid\TreeGrid;
use yii\bootstrap\Modal;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $searchModel backend\models\MenuSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '菜单管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-index">

    <div class="row">
        <div class="col-sm-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li><?=Html::edit(['create','pid' => '0'],"<button class='btn btn-success'>创建顶级菜单</button>",[
                            'data-toggle' => 'modal',
                            'data-target' => '#create-top-modal',
                            'class' => 'data-create-top',
                        ])?></li>
                </ul>
                <div class="tab-content">
                    <div class="active tab-pane">
                        <?= TreeGrid::widget([
                            'dataProvider' => $dataProvider,
                            'keyColumnName' => 'id', //ID
                            'parentColumnName' => 'pid', //父ID
                            'parentRootValue' => '0', //first parentId value
                            'pluginOptions' => [
                                'initialState' => 'expanded', //expanded 展开 ，collapsed 收缩
                            ],
                            'options' => ['class' => 'table table-hover'],
                            'columns' => [
                                [
                                    'attribute' => 'title',
                                    'format' => 'raw',
                                    'headerOptions' => ['class' => 'col-md-4'],
                                    'value' => function ($model, $key, $index, $column){
                                        $str = Html::tag('span', $model->title, [
                                            'class' => 'm-l-sm'
                                        ]);
                                        $str .= Html::a(' <i class="glyphicon glyphicon-plus-sign"></i>', ['create', 'pid' => $model['pid']], [
                                            'title' => Yii::t('yii','添加一行'),
                                            'aria-label' => Yii::t('yii','添加一行'),
                                            'data-toggle' => 'modal',
                                            'data-target' => '#create-modal',
                                            'class' => 'data-create',
                                            'data-id' => $key,
                                        ]);
                                        return $str;
                                    }
                                ],
                                [
                                    'attribute' => 'route',
                                    'format' => 'raw',
                                    'headerOptions' => ['class' => 'col-md-2'],
//                                    'value' => function ($model, $key, $index, $column){
//                                        return Html::input('text','route',$model->route,[ 'class' => 'form-control']);
//                                    }
                                ],
                                [
                                    'attribute' => 'params',
                                    'format' => 'raw',
                                    'headerOptions' => ['class' => 'col-md-2'],
//                                    'value' => function ($model, $key, $index, $column){
//                                        return Html::input('text','route',$model->route,[ 'class' => 'form-control']);
//                                    }
                                ],
                                [
                                    'attribute' => 'icon',
                                    'format' => 'raw',
                                    'headerOptions' => ['class' => 'col-md-1'],
                                    'value' => function ($model, $key, $index, $column){
                                        return  "<span style='text-align: center'><i class='{$model->icon}'></span>";
                                    }
                                ],
                                [
                                    'attribute' => 'sort',
                                    'format' => 'raw',
                                    'headerOptions' => ['class' => 'col-md-1'],
//                                    'value' => function ($model, $key, $index, $column){
//                                        return  Html::sort($model->sort);
//                                    }
                                ],
                                [
                                    'header' => "操作",
                                    'headerOptions' => ['class' => 'col-md-2'],
                                    'class' => 'yii\grid\ActionColumn',
                                    'template'=> '{update} {status} {delete}',
                                    'buttons' => [
                                        'update' => function ($url, $model, $key) {
                                            return Html::edit(['update','id' => $model->id], '编辑', [
                                                'data-toggle' => 'modal',
                                                'data-target' => '#update-modal',
                                                'class' => 'btn btn-primary btn-sm data-update',
                                                'data-id' => $key,
                                            ]);
                                        },
                                        'status' => function ($url, $model, $key) {
                                            $showName = $model->status == 1 ? '禁用' : '启用';
                                            $confirmName = $model->status == 1 ? '确定要禁用吗？' : '确定要启用吗？';
                                            $className = $model->status == 1 ? 'btn btn-default btn-sm' : 'btn btn-success btn-sm';
                                            return Html::delete(['status','id' => $model->id,'status' => $model->status],"{$showName}",[
                                                'data-method' => 'post',
                                                'data-confirm' => Yii::t('yii',"{$confirmName}"),
                                                'class' => $className
                                            ]);
                                        },
                                        'delete' => function ($url, $model, $key) {
                                            return Html::delete(['delete', 'id' => $model->id],'删除',[
                                                    'data-method' => 'post',
                                                    'data-confirm' => Yii::t('yii','你确定要删除吗？'),
                                                ]);
                                        },
                                    ],
                                ],
                            ]
                        ]); ?>
                        <?php
                        //创建操作
                        Modal::begin([
                            'id' => 'create-modal',
                            'header' => '<h4 class="modal-title" style="color: #0d6aad">创建</h4>',
                            'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
                            'size' => 'modal-lg',
                        ]);
                        Modal::end();
                        $createUrl = Url::toRoute(['create']);
                        $createJs = <<<JS
    $('.data-create').on('click', function () {
		var contentHeight = document.body.scrollHeight - 200;
	
        var url = "{$createUrl}" + "&pid=" + $(this).closest('tr').data('key');
        
      $('.modal-body').html('<iframe id="iframe_name_top"  style="width: 100%;' + 'height:' + contentHeight +'px;"' + 'src="' + url + '" frameborder="0"></iframe>');
    
    });
JS;
                        $this->registerJs($createJs);
                        ?>

                        <?php
                        //创建顶级分类操作
                        Modal::begin([
                            'id' => 'create-top-modal',
                            'header' => '<h4 class="modal-title" style="color: #0d6aad">创建</h4>',
                            'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
                            'size' => 'modal-lg',
                        ]);
                        Modal::end();
                        $createTopUrl = Url::toRoute(['create','pid' => '0']);
                        $createTopJs = <<<JS
    $('.data-create-top').on('click', function () {
        var contentHeightTop = document.body.scrollHeight - 220;
        
        var url = "{$createTopUrl}";
        
       $('.modal-body').html('<iframe id="iframe_name_top"  style="width: 100%;' + 'height:' + contentHeightTop +'px;"' + 'src="' + url + '" frameborder="0"></iframe>');
    
    });
JS;
                        $this->registerJs($createTopJs);
                        ?>

                        <?php
                        //编辑操作
                        Modal::begin([
                            'id' => 'update-modal',
                            'header' => '<h4 class="modal-title" style="color: #0d6aad">创建</h4>',
                            'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
                            'size' => 'modal-lg',
                        ]);
                        Modal::end();
                        $updateUrl = Url::toRoute(['update']);
                        $createTopJs = <<<JS
    $('.data-update').on('click', function () {
        var contentHeightUpdate = document.body.scrollHeight - 200;
        
        var url = "{$updateUrl}" + "&id=" + $(this).closest('tr').data('key');
        
       $('.modal-body').html('<iframe style="width: 100%;' + 'height:' + contentHeightUpdate +'px;"' + 'src="' + url + '" frameborder="0"></iframe>');
    });
JS;
                        $this->registerJs($createTopJs);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
