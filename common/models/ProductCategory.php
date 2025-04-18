<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "product_category".
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $image
 * @property int $status
 * @property int|null $sort_order
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property Product[] $products
 * @property ProductCategory $parent
 * @property ProductCategory[] $children
 * @property ProductDiscount[] $productDiscounts
 * @property User $createdBy
 * @property User $updatedBy
 */
class ProductCategory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_category';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'status', 'sort_order', 'created_by', 'updated_by'], 'integer'],
            [['name', 'slug'], 'required'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'slug', 'image'], 'string', 'max' => 255],
            [['slug'], 'unique'],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategory::class, 'targetAttribute' => ['parent_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
            ['parent_id', 'validateParent'],
        ];
    }

    /**
     * Validate that parent is not self or child
     */
    public function validateParent($attribute, $params)
    {
        if (!$this->hasErrors() && $this->$attribute) {
            if ($this->$attribute == $this->id) {
                $this->addError($attribute, 'Danh mục không thể là danh mục cha của chính nó.');
                return;
            }

            // Check if parent is not a child of this category
            $childrenIds = $this->getChildrenIds();
            if (in_array($this->$attribute, $childrenIds)) {
                $this->addError($attribute, 'Danh mục cha không thể là danh mục con của danh mục này.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Danh mục cha',
            'name' => 'Tên danh mục',
            'slug' => 'Slug',
            'description' => 'Mô tả',
            'image' => 'Hình ảnh',
            'status' => 'Trạng thái',
            'sort_order' => 'Thứ tự',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
            'created_by' => 'Người tạo',
            'updated_by' => 'Người cập nhật',
        ];
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Parent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(ProductCategory::class, ['id' => 'parent_id']);
    }

    /**
     * Gets query for [[Children]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(ProductCategory::class, ['parent_id' => 'id']);
    }

    /**
     * Gets query for [[ProductDiscounts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductDiscounts()
    {
        return $this->hasMany(ProductDiscount::class, ['product_category_id' => 'id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Get all children IDs
     * 
     * @return array
     */
    public function getChildrenIds()
    {
        $ids = [];
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getChildrenIds());
        }
        return $ids;
    }

    /**
     * Get category tree
     * 
     * @param bool $onlyActive
     * @return array
     */
    public static function getTree($onlyActive = true)
    {
        $query = self::find()->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);
        
        if ($onlyActive) {
            $query->andWhere(['status' => 1]);
        }
        
        $categories = $query->all();
        
        return self::buildTree($categories);
    }

    /**
     * Build tree array from flat array
     * 
     * @param array $elements
     * @param int $parentId
     * @return array
     */
    protected static function buildTree($elements, $parentId = null)
    {
        $branch = [];
        
        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $children = self::buildTree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                $branch[] = $element;
            }
        }
        
        return $branch;
    }

    /**
     * Get dropdown list
     * 
     * @param bool $onlyActive
     * @return array
     */
    public static function getDropdownList($onlyActive = true)
    {
        $query = self::find()->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);
        
        if ($onlyActive) {
            $query->andWhere(['status' => 1]);
        }
        
        $categories = $query->all();
        
        return self::buildDropdownList($categories);
    }

    /**
     * Build dropdown list array
     * 
     * @param array $categories
     * @param int $parentId
     * @param string $prefix
     * @return array
     */
    protected static function buildDropdownList($categories, $parentId = null, $prefix = '')
    {
        $result = [];
        
        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $result[$category->id] = $prefix . $category->name;
                $result = ArrayHelper::merge(
                    $result,
                    self::buildDropdownList($categories, $category->id, $prefix . '-- ')
                );
            }
        }
        
        return $result;
    }
}