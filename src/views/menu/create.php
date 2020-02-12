<?php


/* @var $this yii\web\View */
/* @var $model backend\models\Menu */

$this->title = $model->pid == '0' ? '创建顶级分类' : '创建《' . $item->title . "》的下级";
?>
<div class="menu-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
