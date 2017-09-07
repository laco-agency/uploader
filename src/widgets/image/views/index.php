<?php

use yii\helpers\Html;

$model = $this->context->model;
$attribute = $this->context->attribute;
$imageUrl = $this->context->imageUrl;
$imageOptions = $this->context->imageOptions;
?>

<div class="form-group">

    <?php
    $id = Html::getInputId($model, $attribute);
    if ($model->$attribute) {
        echo Html::img($imageUrl, array_merge($imageOptions, ['id' => $id . '_image']));
    }
    ?>
    <br>
    <br>

    <span class="btn btn-default btn-file">
        <?= Html::activeHiddenInput($model, $attribute, ['id' => $id . '_hidden', 'value' => $model->$attribute]) ?>
        <?= Html::activeInput('file', $model, $attribute, ['class' => 'form-control']) ?>
        <span class="fileinput-new">Выберите изображение</span>
    </span>

    <?php
    if ($model->$attribute) {
        echo Html::button('удалить', [
            'class' => "btn btn-default btn-remove-file",
            'onclick' => new \yii\web\JsExpression("
                $('#" . $id . "_hidden,#" . $id . "').val('');
                $('#" . $id . "_image').attr('src','');
                this.remove();
            ")
        ]);
    }
    ?>
</div>
<style>
    .btn-file > input {
        position: absolute;
        top: 0;
        right: 0;
        margin: 0;
        opacity: 0;
        filter: alpha(opacity=0);
        font-size: 23px;
        height: 100%;
        width: 100%;
        direction: ltr;
        cursor: pointer;
    }

    .btn-file {
        overflow: hidden;
        position: relative;
        vertical-align: middle;
    }
</style>