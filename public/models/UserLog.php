<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;
use yii\web\Request;

/**
 * This is the model class for table "{{%user_log}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property array|null $log
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $created_at
 */
class UserLog extends ActiveRecord
{
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_PASSWORD_UPDATE = 'password_update';
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'action'], 'required'],
            [['user_id'], 'integer'],
            [['action'], 'string', 'max' => 50],
            [['log'], 'safe'],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 512],
            [['action'], 'in', 'range' => array_keys(self::getActionsList())],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'action' => 'Action',
            'log' => 'Log',
            'ip_address' => 'IP Address',
            'user_agent' => 'User Agent',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        
        if ($this->log !== null && is_array($this->log)) {
            $this->log = Json::encode($this->log);
        }
        
        return true;
    }

    /**
     * Получение списка возможных действий
     * @return array
     */
    public static function getActionsList()
    {
        return [
            self::ACTION_LOGIN => 'Login',
            self::ACTION_LOGOUT => 'Logout',
            self::ACTION_PASSWORD_UPDATE => 'Password Update',
        ];
    }

    /**
     * Запись новой строки в лог
     *
     * @param int $userId ID пользователя
     * @param string $action Действие {{@see self::getActionsList()}}
     * @param array|null $log Дополнительные данные
     * @param Request|null $request Объект запроса
     * @return bool
     * @throws \Exception
     */
    public static function log($userId, $action, $log = null, $request = null)
    {
        $model = new static();
        $model->user_id = $userId;
        $model->action = $action;
        
        if ($log !== null) {
            $model->log = $log;
        }
        
        if ($request === null) {
            $request = Yii::$app->request;
        }
        
        if ($request instanceof Request) {
            $model->ip_address = $request->getUserIP();
            $model->user_agent = $request->getUserAgent();
        }
        
        if (!$model->save()) {
            Yii::error('Log save error: ' . Json::encode($model->errors), __METHOD__);
            throw new \Exception('Log save error');
        }
        
        return true;
    }

    /**
     * Поиск записей лога по ID пользователя
     *
     * @param int [обязательное] $userId ID пользователя
     * @param string|null [необязательное] $action Фильтр по типу действия
     * @param int [необязательное] $limit Лимит записей
     * @return UserLog[]|array
     * @throws \InvalidArgumentException
     */
    public static function findByUserId($userId, $action = null, $limit = 5)
    {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new \InvalidArgumentException('User ID must be integer');
        }
        
        $query = static::find()
            ->where(['user_id' => (int)$userId])
            ->orderBy(['created_at' => SORT_DESC]);
        
        if ($action !== null) {
            if (!in_array($action, array_keys(self::getActionsList()))) {
                throw new \InvalidArgumentException('Invalid action type');
            }
            $query->andWhere(['action' => $action]);
        }

        $query->limit((int)$limit);
        
        return $query->all();
    }

    /**
     * Преобразование данных после получения
     */
    public function afterFind()
    {
        parent::afterFind();
        
        if ($this->log !== null && is_string($this->log)) {
            try {
                $this->log = Json::decode($this->log);
            } catch (\Exception $e) {
                Yii::error("User log not decode: {$e->getMessage()}", __METHOD__);
                $this->log = null;
            }
        }
    }
}