<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $model common\models\RbacAssignment */
/* @var $routes array */
/* @var $roles array */

$this->title = 'Assign Permissions';
$this->params['breadcrumbs'][] = ['label' => 'Role Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Convert permissions array to JSON for JavaScript
$selectedPermissions = Json::encode($model->permissions);

$this->registerCss("
    .permission-tree {
        max-height: 500px;
        overflow-y: auto;
    }
    
    .module-item, .controller-item, .action-item {
        padding: 5px;
        border-radius: 3px;
    }
    
    .module-item {
        background-color: #f5f5f5;
        margin-bottom: 10px;
    }
    
    .controller-item {
        background-color: #f9f9f9;
        margin: 5px 0;
        margin-left: 20px;
    }
    
    .action-item {
        margin-left: 40px;
    }
    
    .permission-checkbox {
        margin-right: 5px;
    }
    
    .permission-name {
        font-family: monospace;
        font-size: 12px;
        color: #666;
        margin-left: 5px;
    }
");

$this->registerJs("
    var selectedPermissions = " . $selectedPermissions . ";
    
    // Pre-select checkboxes
    $.each(selectedPermissions, function(index, permission) {
        $('input[name=\"permission-' + permission.replace(/\\//g, '-') + '\"]').prop('checked', true);
    });
    
    // Toggle modules
    $('.module-check').on('change', function() {
        var checked = $(this).prop('checked');
        var moduleId = $(this).data('module');
        
        // Toggle all controllers in this module
        $('#module-' + moduleId + ' .controller-check').prop('checked', checked).trigger('change');
        
        updateSelectedPermissions();
    });
    
    // Toggle controllers
    $('.controller-check').on('change', function() {
        var checked = $(this).prop('checked');
        var controllerId = $(this).data('controller');
        
        // Toggle all actions in this controller
        $('#controller-' + controllerId + ' .action-check').prop('checked', checked);
        
        // If checked, also check the module
        if (checked) {
            var moduleId = $(this).data('module');
            $('#module-check-' + moduleId).prop('checked', true);
        }
        
        updateSelectedPermissions();
    });
    
    // Update when action is checked
    $('.action-check').on('change', function() {
        var checked = $(this).prop('checked');
        
        // If checked, also check the controller and module
        if (checked) {
            var controllerId = $(this).data('controller');
            var moduleId = $(this).data('module');
            
            $('#controller-check-' + controllerId).prop('checked', true);
            $('#module-check-' + moduleId).prop('checked', true);
        }
        
        updateSelectedPermissions();
    });
    
    // Select all permissions
    $('#select-all').on('click', function(e) {
        e.preventDefault();
        $('.permission-checkbox').prop('checked', true);
        updateSelectedPermissions();
    });
    
    // Deselect all permissions
    $('#deselect-all').on('click', function(e) {
        e.preventDefault();
        $('.permission-checkbox').prop('checked', false);
        updateSelectedPermissions();
    });
    
    // Expand all sections
    $('#expand-all').on('click', function(e) {
        e.preventDefault();
        $('.collapse').collapse('show');
    });
    
    // Collapse all sections
    $('#collapse-all').on('click', function(e) {
        e.preventDefault();
        $('.collapse').collapse('hide');
    });
    
    // Search function
    $('#permission-search').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        
        if (value.length > 0) {
            // Expand all when searching
            $('.collapse').collapse('show');
            
            // Show/hide items based on search
            $('.module-item, .controller-item, .action-item').each(function() {
                var text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(value) > -1);
            });
        } else {
            // Show all items when search is cleared
            $('.module-item, .controller-item, .action-item').show();
        }
    });
    
    // Update the selected permissions
    function updateSelectedPermissions() {
        var modules = [];
        var controllers = [];
        var actions = [];
        
        // Get selected modules
        $('.module-check:checked').each(function() {
            modules.push($(this).val());
        });
        
        // Get selected controllers
        $('.controller-check:checked').each(function() {
            controllers.push($(this).val());
        });
        
        // Get selected actions
        $('.action-check:checked').each(function() {
            actions.push($(this).val());
        });
        
        // Update hidden inputs
        $('#rbacassignment-modules').val(modules.join(','));
        $('#rbacassignment-controllers').val(controllers.join(','));
        $('#rbacassignment-actions').val(actions.join(','));
    }
    
    // Handle role change
    $('#rbacassignment-role').on('change', function() {
        var role = $(this).val();
        if (role) {
            window.location.href = '" . Url::to(['assign']) . "?name=' + role;
        }
    });
    
    // Initialize the form
    updateSelectedPermissions();
    
    // Form submission using AJAX
    $('#assignment-form').on('beforeSubmit', function(e) {
        var form = $(this);
        var formData = form.serialize();
        var submitBtn = form.find('button[type=\"submit\"]');
        var originalText = submitBtn.html();
        
        submitBtn.html('<i class=\"fa fa-spinner fa-spin\"></i> Saving...').attr('disabled', true);
        
        $.ajax({
            url: '" . Url::to(['save-assignments']) . "',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
                
                submitBtn.html(originalText).attr('disabled', false);
            },
            error: function(xhr) {
                toastr.error('Error saving permissions');
                submitBtn.html(originalText).attr('disabled', false);
            }
        });
        
        return false;
    });
");

?>
<div class="rbac-assign">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assign Permissions to Role</h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'assignment-form',
                        'options' => ['class' => 'form-horizontal'],
                    ]); ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'role')->dropDownList(
                                $roles,
                                ['prompt' => '-- Select Role --']
                            ) ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Search Permissions</label>
                                <input type="text" id="permission-search" class="form-control" placeholder="Search for permissions...">
                            </div>
                        </div>
                    </div>
                    
                    <?= Html::hiddenInput('RbacAssignment[modules]', '', ['id' => 'rbacassignment-modules']) ?>
                    <?= Html::hiddenInput('RbacAssignment[controllers]', '', ['id' => 'rbacassignment-controllers']) ?>
                    <?= Html::hiddenInput('RbacAssignment[actions]', '', ['id' => 'rbacassignment-actions']) ?>
                    
                    <div class="btn-group mb-3">
                        <?= Html::a('Select All', '#', ['id' => 'select-all', 'class' => 'btn btn-primary btn-sm']) ?>
                        <?= Html::a('Deselect All', '#', ['id' => 'deselect-all', 'class' => 'btn btn-secondary btn-sm']) ?>
                        <?= Html::a('Expand All', '#', ['id' => 'expand-all', 'class' => 'btn btn-info btn-sm']) ?>
                        <?= Html::a('Collapse All', '#', ['id' => 'collapse-all', 'class' => 'btn btn-warning btn-sm']) ?>
                    </div>
                    
                    <div class="permission-tree">
                        <div class="accordion" id="permission-accordion">
                            <?php foreach ($routes as $moduleId => $moduleData): ?>
                                <div class="module-item" id="module-<?= $moduleId ?>">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-auto">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input permission-checkbox module-check" 
                                                    id="module-check-<?= $moduleId ?>" 
                                                    data-module="<?= $moduleId ?>"
                                                    value="<?= $moduleId ?>/*">
                                                <label class="custom-control-label" for="module-check-<?= $moduleId ?>">
                                                    <strong><?= Html::encode($moduleData['name']) ?> Module</strong>
                                                </label>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse-<?= $moduleId ?>">
                                                <i class="fa fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div id="collapse-<?= $moduleId ?>" class="collapse" data-parent="#permission-accordion">
                                        <?php foreach ($moduleData['controllers'] as $controllerId => $controllerData): ?>
                                            <div class="controller-item" id="controller-<?= $moduleId ?>-<?= $controllerId ?>">
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-auto">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input permission-checkbox controller-check" 
                                                                id="controller-check-<?= $moduleId ?>-<?= $controllerId ?>" 
                                                                data-module="<?= $moduleId ?>"
                                                                data-controller="<?= $moduleId ?>-<?= $controllerId ?>"
                                                                value="<?= $controllerData['permission'] ?>"
                                                                name="permission-<?= str_replace('/', '-', $controllerData['permission']) ?>">
                                                            <label class="custom-control-label" for="controller-check-<?= $moduleId ?>-<?= $controllerId ?>">
                                                                <?= Html::encode($controllerData['name']) ?>
                                                            </label>
                                                        </div>
                                                        <span class="permission-name"><?= Html::encode($controllerData['permission']) ?></span>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse-<?= $moduleId ?>-<?= $controllerId ?>">
                                                            <i class="fa fa-chevron-down"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <div id="collapse-<?= $moduleId ?>-<?= $controllerId ?>" class="collapse" data-parent="#collapse-<?= $moduleId ?>">
                                                    <?php foreach ($controllerData['actions'] as $actionId => $actionData): ?>
                                                        <div class="action-item">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input permission-checkbox action-check" 
                                                                    id="action-check-<?= $moduleId ?>-<?= $controllerId ?>-<?= $actionId ?>" 
                                                                    data-module="<?= $moduleId ?>"
                                                                    data-controller="<?= $moduleId ?>-<?= $controllerId ?>"
                                                                    value="<?= $actionData['permission'] ?>"
                                                                    name="permission-<?= str_replace('/', '-', $actionData['permission']) ?>">
                                                                <label class="custom-control-label" for="action-check-<?= $moduleId ?>-<?= $controllerId ?>-<?= $actionId ?>">
                                                                    <?= Html::encode($actionData['name']) ?>
                                                                </label>
                                                                <span class="permission-name"><?= Html::encode($actionData['permission']) ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group mt-4">
                        <?= Html::submitButton('<i class="fa fa-save"></i> Save Permissions', ['class' => 'btn btn-success']) ?>
                        <?= Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
                    </div>
                    
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>