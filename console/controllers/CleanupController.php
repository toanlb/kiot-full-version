<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\LoginHistory;

class CleanupController extends Controller
{
    /**
     * Clean up old login history records
     * 
     * @param int $days Number of days to keep. Default: 90 days
     * @return int
     */
    public function actionLoginHistory($days = 90)
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");
        $dateLimit = $date->format('Y-m-d');
        
        $count = LoginHistory::deleteAll(['<', 'login_time', $dateLimit]);
        
        $this->stdout("Deleted {$count} old login history records.\n");
        return 0;
    }
}