<?php

namespace hesabro\ticket;

use Closure;
use hesabro\ticket\models\Comments;
use hesabro\ticket\models\Tickets;
use Yii;
use yii\base\InvalidConfigException;
use yii\grid\GridView;
use yii\helpers\Url;

/**
 * To use stocks:
 * ```php
 * 'modules' => [
 *        // .... other modules
 *         "tickets" => [
 *             'class' => 'hesabroModules\ticket\TicketModule',
 *         ],
 *        // .... other modules
 * ]
 * ```
 */
class TicketModule extends \yii\base\Module
{
	public $controllerNamespace = 'hesabro\ticket\controllers';

    public $defaultRoute = 'ticket/index';
    /**
     * @var string the URL for getting users
     * response should be a JSON like this:
     * [
     *    "results": [
     *      {'id':1,'text':'John Doe'},
     *      {'id':2,'text':'Jane Doe'}
     *    ],
     * ]
     */
    public string $getUsersUrl;

    public string $db = 'db';


    public string | null $user = null;

    public ?string $comfortItemsClass;
    public ?string $authAssignmentClass;
    public ?string $authItemChildClass;
    public array $ticketsRole = [];
    public ?string $clientComponentClass = null;
    public ?string $commentsMasterClass = null;
    public ?string $commentsViewMasterClass = null;
    public ?array $notificationBehavior = null;
    public ?bool $hasSlaves = false;
    public ?Closure $notifyToSupporter = null;

	public function init()
	{
        if(!Yii::$app->has('user')){
            throw new InvalidConfigException('User component must be configured');
        }

		parent::init();
	}

    public static function createUrl(string $path = null, array $params = [])
    {
        $moduleId = self::getInstance()?->id;

        $path = trim($path ?: '', '/');
        return Url::to([rtrim("/$moduleId/$path", '/'), ...$params]);
    }

    public static function getNotifEvents()
    {
        return Tickets::itemAlias('Notif') ?: [];
    }
}
