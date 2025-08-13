<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\UserLog;

class SiteController extends Controller
{
    const REQUEST_LIMIT = 2; //2 запроса в минуту

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->checkRequestLimit();
        \Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (!\Yii::$app->request->isGet) {
            throw new BadRequestHttpException('Метод запроса должен быть GET');
        }

        $name = \Yii::$app->request->get('name');
        
        if ($name === null) {
            return [
                'status' => 'error',
                'name' => $name,
                'message' => 'Не передан обязательный параметр name',
                'timestamp' => time(),
            ];
        }
        
        if (!is_string($name)) {
            return [
                'status' => 'error',
                'name' => $name,
                'message' => 'Параметр name должен быть строкой',
                'timestamp' => time(),
            ];
        }
        
        if (trim($name) === '') {
            return [
                'status' => 'error',
                'name' => $name,
                'message' => 'Параметр name не может быть пустой строкой',
                'timestamp' => time(),
            ];
        }
        
        return [
            'status' => 'success',
            'name' => $name,
            'message' => 'ok',
            'timestamp' => time(),
        ];
    }

    public function actionCreateLog()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $log = ['field' => 'password', 'message' => 'log about password update'];
            UserLog::log(Yii::$app->user->id, UserLog::ACTION_PASSWORD_UPDATE, $log);
            return [
                'status' => 'success',
                'message' => 'Successful log saving',
                'timestamp' => time(),
            ];
        } catch (\Exception $e) {
            Yii::error("Log save error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Log save error',
                'timestamp' => time(),
            ];
        }
    }

    public function actionSearchLog()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $userId = 111;
            $logs = UserLog::findByUserId($userId, UserLog::ACTION_PASSWORD_UPDATE);
           
            foreach ($logs as $log) {
                echo "Action: {$log->action}, Date: {$log->created_at}, IP: {$log->ip_address}\n";
            }
        } catch (\InvalidArgumentException $e) {
            Yii::error("Invalid arguments for log search: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'User log not found',
                'timestamp' => time(),
            ];
        }
    }

    protected function checkRequestLimit()
    {
        $cache = \Yii::$app->cache;
        $ip = \Yii::$app->request->getUserIP();
        $key = "request_limit:$ip";
        
        $requests = $cache->get($key) ?: 0;
        
        if ($requests >= self::REQUEST_LIMIT) {
            throw new TooManyRequestsHttpException('Слишком много запросов. Попробуйте позже.');
        }
        
        $cache->set($key, $requests + 1, 60);
    }
}
