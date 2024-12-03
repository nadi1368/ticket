<?php

use hesabro\ticket\models\Tickets;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $comments Tickets[] */
/* @var $form yii\bootstrap4\ActiveForm */
?>
<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <tbody>
            <?php foreach ($comments as $comment):
				/** @var Tickets $comment */
				?>
                <?php $css_class = Tickets::itemAlias('PriorityClass', $comment->priority) ?>
                <tr class="<?= $css_class ?>">
                    <td width="80%" class="<?= $css_class ?>"><?= $comment->des . (($fileUrl = $comment->getFileUrl('file')) ? Html::a('<span class="badge badge-info">دانلود فایل پیوست</span>', $fileUrl, ['class' => 'pull-left']) : '') ?> </td>
                    <td class="<?= $css_class ?>">
                        <?= $comment->creator->fullName . ' - ' . Yii::$app->jdate->date('Y/m/d H:i', $comment->created); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
