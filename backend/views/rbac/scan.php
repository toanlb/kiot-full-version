<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\RbacScan */
/* @var $scanResults array */

$this->title = 'Scan Permissions';
$this->params['breadcrumbs'][] = ['label' => 'Role Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("
    $('#scan-form').on('beforeSubmit', function(e) {
        var form = $(this);
        var formData = form.serialize();
        
        $('#scan-results').html('<div class=\"text-center\"><i class=\"fa fa-spinner fa-spin fa-3x\"></i><br>Scanning in progress...</div>');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                $('#scan-results').html(data);
            },
            error: function(xhr) {
                $('#scan-results').html('<div class=\"alert alert-danger\">Error: ' + xhr.responseText + '</div>');
            }
        });
        
        return false;
    });
    
    $('#update-permissions-btn').on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        var originalText = btn.html();
        
        btn.html('<i class=\"fa fa-spinner fa-spin\"></i> Updating...').attr('disabled', true);
        
        $.ajax({
            url: '" . Url::to(['update-permissions']) . "',
            type: 'POST',
            data: $('#scan-form').serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    
                    // Show stats
                    var statsHtml = '<div class=\"alert alert-success\">' +
                        '<h5><i class=\"fa fa-check\"></i> Permissions Updated</h5>' +
                        '<ul>' +
                        '<li>Created: ' + response.stats.created + '</li>' +
                        '<li>Updated: ' + response.stats.updated + '</li>' +
                        '<li>Unchanged: ' + response.stats.unchanged + '</li>' +
                        '<li>Hierarchies: ' + response.stats.hierarchies + '</li>' +
                        '</ul></div>';
                    
                    $('#update-results').html(statsHtml);
                } else {
                    toastr.error(response.message);
                    $('#update-results').html('<div class=\"alert alert-danger\">' + response.message + '</div>');
                }
                
                btn.html(originalText).attr('disabled', false);
            },
            error: function(xhr) {
                toastr.error('Error updating permissions');
                $('#update-results').html('<div class=\"alert alert-danger\">Error: ' + xhr.responseText + '</div>');
                btn.html(originalText).attr('disabled', false);
            }
        });
    });
");

?>
<div class="rbac-scan">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Scan Options</h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'scan-form',
                        'options' => ['class' => 'form-horizontal'],
                    ]); ?>
                    
                    <?= $form->field($model, 'modules')->checkboxList([
                        'frontend' => 'Frontend',
                        'backend' => 'Backend',
                        'api' => 'API',
                    ]) ?>
                    
                    <?= $form->field($model, 'onlyNew')->checkbox() ?>
                    
                    <?= $form->field($model, 'createHierarchy')->checkbox() ?>
                    
                    <div class="form-group">
                        <?= Html::submitButton('<i class="fa fa-search"></i> Scan System', ['class' => 'btn btn-primary']) ?>
                        <?= Html::button('<i class="fa fa-save"></i> Update Permissions', [
                            'id' => 'update-permissions-btn',
                            'class' => 'btn btn-success',
                            'style' => $scanResults ? '' : 'display: none;'
                        ]) ?>
                    </div>
                    
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            
            <div id="update-results" class="mt-3"></div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Scan Results</h3>
                </div>
                <div class="card-body" id="scan-results">
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
                </div>
            </div>
        </div>
    </div>
</div>