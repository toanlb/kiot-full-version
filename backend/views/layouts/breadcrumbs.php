<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <?php if (isset($this->params['icon'])): ?>
                        <i class="<?= $this->params['icon'] ?>"></i>
                    <?php endif; ?>
                    <?= $this->title ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= Yii::$app->homeUrl ?>"><i class="fas fa-home"></i></a></li>
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
        <?php if (isset($this->params['description'])): ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-info"></i> Thông tin!</h5>
                    <?= $this->params['description'] ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>