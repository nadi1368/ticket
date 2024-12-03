<?php

namespace hesabro\ticket;

use Closure;
use hesabro\ticket\models\Comments;
use Yii;
use yii\base\InvalidConfigException;
use yii\grid\GridView;

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
    public $layout = '@hesabro/ticket/views/layouts/main';

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
    public ?string $comfortItemsClass;
    public ?string $authAssignmentClass;
    public ?string $authItemChildClass;
    public string $getCommentsPermission = 'comments/get';
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
}
