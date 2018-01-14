<?php

use yii\helpers\Html;

$model     = $this->context->model;
$attribute = $this->context->attribute;
$url       = $this->context->url;
$options   = $this->context->options;
?>

<div class="form-group">

	<?php
	$id = Html::getInputId($model, $attribute);
	echo Html::tag('div', $model->$attribute, array_merge($options, ['id' => $id . '_filename']));
	?>
    <br>
    <br>

    <span class="btn btn-default btn-file">
        <?= Html::activeHiddenInput($model, $attribute, ['id' => $id . '_hidden', 'value' => $model->$attribute]) ?>
		<?= Html::activeInput('file', $model, $attribute, [
			'class'    => 'form-control',
			'onchange' => new \yii\web\JsExpression("		                
                $('#" . $id . "_filename').html(this.files[0].name);                
            ")
		]); ?>
        <span class="fileinput-new">Выберите файл</span>
    </span>

	<?php
	if ($model->$attribute)
	{
		echo Html::button('удалить', [
			'class'   => "btn btn-default btn-remove-file",
			'onclick' => new \yii\web\JsExpression("
                $('#" . $id . "_hidden,#" . $id . "').val('');
                $('#" . $id . "_filename').html('');
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