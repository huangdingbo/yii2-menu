<?php

/* @var $this yii\web\View */
/* @var $model backend\models\Menu */

$this->title = '编辑菜单:《' . $model->title . '》';
?>
<div class="menu-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
