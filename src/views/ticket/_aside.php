<?php



/* @var $this \yii\web\View */
/* @var $searchModel \hesabro\ticket\models\CommentsSearch */

?>

<aside class="sm-side">
	<div class="user-head">
		<div class="user-name">
			<h5><?= Yii::$app->user->identity->fullName ?></h5>
		</div>
	</div>
	<?= $this->render('_menu') ?>
	<div class="incard-body p-3">
		<?= $this->render('_search', ['model' => $searchModel]) ?>
	</div>

</aside>
