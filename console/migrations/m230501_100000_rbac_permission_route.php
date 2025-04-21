<?php

use yii\db\Migration;

/**
 * Class m230501_100000_rbac_permission_route
 * 
 * This migration creates a table to track permission routes in the RBAC system.
 * The table helps with mapping controller/action to permissions and tracking
 * which permissions have been scanned and created.
 */
class m230501_100000_rbac_permission_route extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // Create permission route table
        $this->createTable('{{%rbac_permission_route}}', [
            'id' => $this->primaryKey(),
            'permission_name' => $this->string(128)->notNull(),
            'module' => $this->string(64)->notNull(),
            'controller' => $this->string(64)->notNull(),
            'action' => $this->string(64)->null()->defaultValue(null),
            'is_controller' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        // Add indexes
        $this->createIndex(
            'idx-rbac_permission_route-permission_name',
            '{{%rbac_permission_route}}',
            'permission_name',
            true // Unique index
        );
        
        $this->createIndex(
            'idx-rbac_permission_route-module',
            '{{%rbac_permission_route}}',
            'module'
        );
        
        $this->createIndex(
            'idx-rbac_permission_route-controller',
            '{{%rbac_permission_route}}',
            'controller'
        );
        
        $this->createIndex(
            'idx-rbac_permission_route-action',
            '{{%rbac_permission_route}}',
            'action'
        );
        
        $this->createIndex(
            'idx-rbac_permission_route-is_controller',
            '{{%rbac_permission_route}}',
            'is_controller'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%rbac_permission_route}}');
    }
}