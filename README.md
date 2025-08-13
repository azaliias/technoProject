<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">API для обработки имени и логирования действий пользователя на Yii2</h1>
    <br>
</p>

## Установка
1. Клонируйте репозиторий:
```
git clone https://github.com/azaliias/technoProject.git
```
2. Установка зависимостей:
```
composer install
```
3. Настройте подключение к БД в файле `config/db.php`

## Задача 1
Напишите экшен в контроллере SiteController, который:

1. Принимает GET-параметр name (строка).
2. Возвращает ответ в формате json. В ответе должно быть переданное имя.
3. Предусмотреть все возможные ошибки.

## Задача 2
Создайте модель UserLog для таблицы user_log

Реализовать метод для записи новой строки в лог.
Реализовать поиск записей лога по id пользователя.
Предусмотреть все возможные ошибки.

# API Endpoints

### 1. Получение имени
**Endpoint**: `GET {base_url}/`

Параметры:
* **name** (строка, обязательный) - имя для обработки

Пример успешного ответа:
```json
{
  "status": "success",
  "name": "John",
  "message": "ok",
  "timestamp": 1634567890
}
```

Возможные ошибки:
* Не передан параметр name
* Параметр name не является строкой
* Параметр name - пустая строка
* Превышен лимит запросов (ограничение 2 запроса в минуту)

### 2. Создание записи в логе
**Endpoint**: `GET {base_url}/create`

Параметры:
Заданы по умолчанию в контроллере

```json
{
  "status": "success",
  "message": "Successful log saving",
  "timestamp": 1634567890
}
```

### 3. Поиск записей в логе
**Endpoint**: `GET {base_url}/search`

Параметры:
Заданы по умолчанию в контроллере

Система логирует следующие действия:
* Вход в систему (ACTION_LOGIN)
* Выход из системы (ACTION_LOGOUT)
* Изменение пароля (ACTION_PASSWORD_UPDATE)
