<?php
namespace frontend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use common\models\Shift;

class ShiftInfoWidget extends Widget
{
    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $shift = Shift::findActive(Yii::$app->user->identity->warehouse_id, Yii::$app->user->id);
        
        if (!$shift) {
            return $this->renderNoShift();
        }
        
        return $this->renderShiftInfo($shift);
    }
    
    protected function renderNoShift()
    {
        return Html::tag('div', 
            Html::tag('div', 
                Html::tag('span', 'Không có ca làm việc nào được mở', ['class' => 'text-danger']) . ' ' .
                Html::a('<i class="fas fa-play"></i> Mở ca', ['/shift/open'], ['class' => 'btn btn-success btn-sm']),
            ['class' => 'card-body p-2']),
        ['class' => 'card bg-warning mb-3']);
    }
    
    protected function renderShiftInfo($shift)
    {
        $startTime = Yii::$app->formatter->asDatetime($shift->start_time);
        
        $content = Html::tag('div', 
            Html::tag('div', 
                Html::tag('div', 
                    Html::tag('h5', 'Ca #' . $shift->id . ' đang mở', ['class' => 'mb-0']) .
                    Html::tag('small', 'Bắt đầu: ' . $startTime),
                ['class' => 'col']) .
                Html::tag('div', 
                    Html::tag('div', 
                        Html::tag('span', 'Tiền đầu ca: ' . Yii::$app->formatter->asCurrency($shift->opening_amount), ['class' => 'mr-3']) .
                        Html::tag('span', 'Doanh thu: ' . Yii::$app->formatter->asCurrency($shift->total_sales)),
                    ['class' => 'text-right']) .
                    Html::tag('div', 
                        Html::a('<i class="fas fa-eye"></i> Xem', ['/shift/view', 'id' => $shift->id], ['class' => 'btn btn-primary btn-sm mr-1']) .
                        Html::a('<i class="fas fa-lock"></i> Đóng ca', ['/shift/close', 'id' => $shift->id], ['class' => 'btn btn-warning btn-sm']),
                    ['class' => 'mt-1']),
                ['class' => 'col-auto']),
            ['class' => 'row align-items-center']),
        ['class' => 'card-body p-2']);
        
        return Html::tag('div', $content, ['class' => 'card bg-success text-white mb-3']);
    }
}