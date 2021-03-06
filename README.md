Yii2 Queue Mailer
=================

Компонент отправки почты путем постановки ее в очередь.

Компонент полностью имплементирует весь функционал мейлера и может быть использован
как основной мейлер, без потери привычного функционала.

Установка
---------

Для подключения компонентов в свой код необходимо добавить в _composer.json_ следующий код:
```
"oleg-chulakov-studio/yii2-queue-mailer": "dev-master"
```

Или набрать команду:
```
composer require oleg-chulakov-studio/yii2-queue-mailer
```

Выполнение миграций:
```
php yii migrate/up --migrationPath="@vendor/oleg-chulakov-studio/yii2-queue-mailer/src/migrations"
```

Настройка
---------

Майлер зависит от нескольких компонентов, которые так же необходимо
настроить и передать их названия через конфигурацию.

**queue**

В первую очередь зависимость касается непосредственно компонента постановки задания в очередь.
Подробно про его конфигурацию можно почитать в официальной документации: [Yii2-Queue](https://github.com/yiisoft/yii2-queue/blob/master/docs/guide-ru/usage.md)

**mailer**

Вторым важным компонентом является непосредственно [Swift Mailer](https://github.com/yiisoft/yii2-swiftmailer).
Через который будет осуществляться отправка почты из задания в очереди. К тому же данный компонент может
отправить письмо автоматически через него, если не будет настроен компонент очереди или процедура постановки
в очередь прервется и не будет выполнена. Данное поведение можно отключить через конфигурацию.

**attachment**

Третий желаемый компонент находится в этом пакете и является хранилищем и обработчиком
прикрепляемых (внедряемых) файлов к письму. Поскольку мейлер имеет возможность прикрепить файлы
не сохраненные на диске, хранить такие файлы в базе данных не выгодно и они могут быть
сохранены во временном каталоге и в дальнейшем успешно прикреплены к оригинальному сообщению.
Так же компонент может продублировать все файлы во временный каталог, если необходимо сохранять
текущие версии файлов для отправки.

```
'attachment' => [
    'class' => 'chulakov\queuemailer\AttacheStorage',
    'storageAll' => false,
    'storageClear' => true,
    'storagePath' => '@runtime/attachments',
]
```

- **storageAll** - Копировать ли все прикрепляемые файлы. По умолчанию `false`.
- **storageClear** - Необходимость удаление всех временных файлов сообщения после отправки. По умолчанию `true`.
- **storagePath** - Папка в которую будут сохранены прикрепляемые файлы. По умолчанию `@runtime/attachments`.
Для каждого письма __со вложениями__ будет создана своя папка.

**queuemailer**

Настройка компонента отложенной отправки почты имеет множество похожих параметров при настройке:

```
'queuemailer' => [
    'class' => 'chulakov\queuemailer\Mailer',
    'messageClass' => 'chulakov\queuemailer\Message',
    'storageClass' => 'chulakov\queuemailer\models\QueueMail',
    'jobClass' => 'chulakov\queuemailer\jobs\MessageJob',
    // Базовый мейлер
    'viewPath' => '@common/mail',
    'useFileTransport' => false,
    'messageConfig' => [
        ...
    ],
    // Настройка компонентов
    'attacheComponent' => 'attachment',
    'mailerComponent' => 'mailer',
    'componentName' => 'queuemailer',
    'queueComponent' => 'queue',
],
```

- **messageClass** - Класс сообщения, используемый для компоновки сообщения перед отправкой.
- **storageClass** - Класс модели, в которой будет храниться вся информация о сформированном сообщении.
Должен реализовать интерфейс `MailStorageInterface`, реализуя метод `findById`, который должен вернуть
модель с сохраненными данными по ее ID.
- **jobClass** - Класс задания, которое будет обрабатывать отправку сообщения из очереди.
Должно реализовать интерфейс `MessageJobInterface`, реализуя метод `create` для создания задания.
Класс можно поменять на лету, перед каждой отправкой сообщения, через функцию `setJobClass`.
- **viewPath** - Устанавливает папку с шаблонами письма. При отсроченной отправке, данную настройку
достаточно указать только в данном компоненте, поскольку `mailerComponent` не будет осуществлять
генерацию письма, а только заниматься его отправкой.
- **useFileTransport** - Если данный параметр установлен в `true`, то компонент не будет
пытаться отправить почту через компонент `mailerComponent`, а только пытаться сохранить его в базу.
- **messageConfig** - Может так же, как и базовый класс сообщения, получать все настройки письма,
но так же приобретает возможность установить конфигурацию для сериализатора полем `serializer`.
Компонент сериализации должен реализовать интерфейс `SerializerInterface`.
- **attacheComponent** - Компонент обработки прикрепляемых файлов.
Если не будет настроен, то будет возможность прикреплять только существующие файлы на диске.
- **mailerComponent** - Имя компонента для отправки почты. Должен ссылаться на `Yii2-swiftmailer`.
Если компонент не будет настроен, сообщения, что не попадут в очередь, вернут `false` при отправке сообщения.
Так же может не настраиваться при указании `'useFileTransport' => true`, поскольку не
будет осуществляться попытки отправить реальное сообщение.
- **componentName** - Имя текущего или другого компонента, доступного из консольного приложения.
Название будет храниться в базе с заданием и использоваться при извлечении сообщения и отправки почты
через вложеннего мейлера (@see mailerComponent, который обязательно должен быть настроен в консольном приложении).
- **queueComponent** - Имя компонента, используемого для постановки сообщения в очередь.

Если все сообщения планируется отправлять через отложенную отправку,
возможно настроить компонент мейлером по умолчанию, зарегестрировав его под именем `mailer`
и передав, наспример, в `'mailerComponent' => 'swiftmailer'`.
