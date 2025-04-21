<?php
use yii\helpers\Html;
?>

<?php if ($scanResults): ?>
    <div class="row">
        <div class="col-md-6">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fa fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Routes</span>
                    <span class="info-box-number"><?= count($scanResults['routes']) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fa fa-plus-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">New Routes</span>
                    <span class="info-box-number"><?= count($scanResults['newPermissions']) ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($scanResults['newPermissions'])): ?>
        <h5>New Permissions Found</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="60%">Permission Name</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scanResults['newPermissions'] as $name => $description): ?>
                        <tr>
                            <td><code><?= Html::encode($name) ?></code></td>
                            <td><?= Html::encode($description) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <h5>Route Structure</h5>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Controller</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scanResults['routes'] as $controller => $data): ?>
                    <tr>
                        <td>
                            <strong><?= Html::encode($data['name']) ?></strong>
                            <br>
                            <code><?= Html::encode($controller) ?></code>
                            <br>
                            <span class="badge <?= isset($scanResults['existingPermissions'][$controller]) ? 'badge-success' : 'badge-warning' ?>">
                                <?= isset($scanResults['existingPermissions'][$controller]) ? 'Exists' : 'New' ?>
                            </span>
                        </td>
                        <td>
                            <ul class="list-unstyled">
                                <?php foreach ($data['actions'] as $action => $actionName): ?>
                                    <li>
                                        <strong><?= Html::encode($actionName) ?></strong>
                                        <br>
                                        <code><?= Html::encode($action) ?></code>
                                        <br>
                                        <span class="badge <?= isset($scanResults['existingPermissions'][$action]) ? 'badge-success' : 'badge-warning' ?>">
                                            <?= isset($scanResults['existingPermissions'][$action]) ? 'Exists' : 'New' ?>
                                        </span>
                                    </li>
                                    <hr>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <h5><i class="icon fa fa-info"></i> No Scan Results</h5>
        Configure the scan options and click "Scan System" to start scanning for controllers and actions.
    </div>
<?php endif; ?>