<?php

namespace laco\uploader\controllers;

use laco\uploader\storage\CommonStorage;
use laco\uploader\storageFile\StorageFile;
use Yii;
use laco\uploader\sourceFile\UploadedFile;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class UploadController extends Controller
{
    public function actionCommon()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $uploadedFile = UploadedFile::getInstanceByName(Yii::$app->request->post('uploadFileName'));
        if (!$uploadedFile) {
            return [
                'error_code' => 1,
                'error_messages' => ['Загружаемый файл не найден.'],
                'result' => []
            ];
        }
        $storageFile = new StorageFile(['storage' => CommonStorage::className()]);
        if($storageFile->save($uploadedFile)){
            return [
                'error_code' => 0,
                'error_messages' => [],
                'result' => ['url'=>$storageFile->getUrl(), 'fileName'=>$storageFile->getName()]
            ];
        }
    }
}