<?php

use hesabro\ticket\models\Tickets;
use yii\helpers\Html;
use yii\helpers\Url;

$action = Yii::$app->controller->action->id;
$inbox_count = Tickets::countInbox();
$doing_count = Tickets::countInbox(Tickets::STATUS_DOING);
?>
<?php if ($action != 'view') : ?>
	<div class="incard-body p-3">
		<?= Html::a('ارسال', ['ticket/send'], [
			'title' => 'ارسال پیام',
			'class' => 'btn btn-compose showModalButton',
		]);
		?>
	</div>
<?php endif; ?>
<ul class="inbox-nav inbox-divider p-3">
	<li class="<?= $action == 'inbox' ? 'active' : '' ?>">
		<a href="<?= Url::to(['inbox']) ?>">
			<i class="fa fa-inbox"></i>
			<?= Yii::t("app", "Inbox") ?>
			<?php if ($inbox_count > 0): ?>
				<span class="badge badge-danger pull-left"><?= $inbox_count; ?></span>
			<?php endif; ?>
		</a>
	</li>
	<li class="<?= $action == 'doing' ? 'active' : '' ?>">
		<a href="<?= Url::to(['doing']) ?>">
			<i class="fa fa-inbox"></i>
			<?= Yii::t("app", "Ticket Doing") ?>
			<?php if ($doing_count > 0): ?>
				<span class="badge badge-danger pull-left"><?= $doing_count; ?></span>
			<?php endif; ?>
		</a>
	</li>
	<li class="<?= $action == 'closed' ? 'active' : '' ?>">
		<a href="<?= Url::to(['closed']) ?>">
			<i class="fa fa-envelope-o"></i>
			<?= Yii::t("app", "Ticket Closed") ?>
		</a>
	</li>
	<li class="<?= $action == 'outbox' ? 'active' : '' ?>">
		<a href="<?= Url::to(['outbox']) ?>">
			<i class="fa fa-envelope-o"></i>
			<?= Yii::t("app", "Outbox") ?>
		</a>
	</li>
</ul>
