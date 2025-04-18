<?php
namespace backend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use common\models\Stock;
use common\models\Product;
use common\models\Warehouse;

class StockStatusWidget extends Widget
{
    public $product_id;
    public $warehouse_id;
    public $showQuantity = true;
    public $showStatus = true;
    
    public function run()
    {
        $stock = Stock::findOne(['product_id' => $this->product_id, 'warehouse_id' => $this->warehouse_id]);
        $product = Product::findOne($this->product_id);
        $warehouse = Warehouse::findOne($this->warehouse_id);
        
        if (!$product || !$warehouse) {
            return '<span class="text-muted">Không có thông tin</span>';
        }
        
        $output = '';
        
        // Hiển thị số lượng
        if ($this->showQuantity) {
            $quantity = $stock ? $stock->quantity : 0;
            $unit = $product->unit->abbreviation;
            
            $output .= '<span class="stock-quantity">' . $quantity . ' ' . $unit . '</span>';
        }
        
        // Hiển thị trạng thái
        if ($this->showStatus) {
            $minStock = $stock && $stock->min_stock ? $stock->min_stock : $product->min_stock;
            $quantity = $stock ? $stock->quantity : 0;
            
            $statusHtml = '';
            if ($quantity <= 0) {
                $statusHtml = '<span class="badge badge-danger">Hết hàng</span>';
            } elseif ($quantity <= $minStock) {
                $statusHtml = '<span class="badge badge-warning">Sắp hết</span>';
            } else {
                $statusHtml = '<span class="badge badge-success">Còn hàng</span>';
            }
            
            if ($output) {
                $output .= ' ';
            }
            
            $output .= $statusHtml;
        }
        
        return '<div class="stock-status">' . $output . '</div>';
    }
}