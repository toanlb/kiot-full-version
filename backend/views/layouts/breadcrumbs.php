<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $this->title ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= Yii::$app->homeUrl ?>">Trang chủ</a></li>
                    <?php if (isset($this->params['breadcrumbs'])): ?>
                        <?php foreach ($this->params['breadcrumbs'] as $item): ?>
                            <?php if (isset($item['label']) && isset($item['url'])): ?>
                                <li class="breadcrumb-item">
                                    <a href="<?= \yii\helpers\Url::to($item['url']) ?>"><?= $item['label'] ?></a>
                                </li>
                            <?php else: ?>
                                <li class="breadcrumb-item active"><?= $item ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="breadcrumb-item active"><?= $this->title ?></li>
                    <?php endif; ?>
                </ol>
            </div>
        </div>
    </div>
</div>