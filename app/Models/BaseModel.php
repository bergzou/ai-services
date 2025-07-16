<?php

namespace App\Models;

use App\Services\CommonService;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 基础模型类：提供通用数据库查询配置与操作方法
 * 支持链式设置查询参数（分页/排序/字段/联表等），封装分页、单条/多条记录查询逻辑
 */
class BaseModel extends Model
{
    /**
     * 表别名（用于联表查询时标识主表）
     * @var string
     */
    protected string $alias;

    /**
     * 设置表别名（链式调用）
     * @param string $alias 表别名（如 "u" 表示 users 表的别名）
     * @return $this 支持链式调用
     */
    public function setAlias(string $alias): BaseModel
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * 当前页码（分页查询参数）
     * @var int 默认第1页
     */
    protected int $current = 1;

    /**
     * 设置当前页码（链式调用）
     * @param int $current 页码值（从1开始）
     * @return $this 支持链式调用
     */
    public function setCurrent($current): BaseModel
    {
        $this->current = $current;
        return $this;
    }

    /**
     * 每页显示数量（分页查询参数）
     * @var int 默认20条/页
     */
    protected int $size = 20;

    /**
     * 设置每页显示数量（链式调用）
     * @param int $size 每页记录数
     * @return $this 支持链式调用
     */
    public function setSize($size): BaseModel
    {
        $this->size = $size;
        return $this;
    }

    /**
     * 排序规则（字段名 => 排序方向）
     * @var array 默认按id降序排序
     */
    protected array $orderBy = ['id' => 'desc'];

    /**
     * 设置排序规则（链式调用）
     * @param array $orderBy 排序数组（如 ['name' => 'asc', 'created_at' => 'desc']）
     * @return $this 支持链式调用
     */
    public function setOrderBy($orderBy): BaseModel
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * 分组字段（用于GROUP BY查询）
     * @var string 默认不分组
     */
    protected string $group = '';

    /**
     * 设置分组字段（链式调用）
     * @param string $group 分组字段名（如 "category_id"）
     * @return $this 支持链式调用
     */
    public function setGroup($group): BaseModel
    {
        $this->group = $group;
        return $this;
    }

    /**
     * 查询字段（指定要查询的列）
     * @var array|string 默认查询所有字段（'*'）
     */
    protected $fields = ['*'];

    /**
     * 设置查询字段（链式调用）
     * @param array|string $fields 字段数组（如 ['id', 'name']）或SQL表达式（如 'COUNT(*) as total'）
     * @return $this 支持链式调用
     */
    public function setFields($fields): BaseModel
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * 转换后的查询条件数组（由convertConditions生成）
     * @var array 格式：['字段名' => ['search' => 查询类型, 'field' => 数据库字段, 'operator' => 操作符, 'value' => 值]]
     */
    protected array $conditions = [];

    /**
     * 将输入参数转换为查询条件数组（链式调用）
     * @param array $input 原始输入参数（如请求参数）
     * @param array $whereMap 条件映射配置（格式：['输入字段名' => ['field' => 数据库字段, 'search' => 查询类型, 'operator' => 操作符]]）
     * @return $this 支持链式调用
     */
    public function convertConditions(array $input = [], array $whereMap = []): BaseModel
    {
        $conditions = [];
        foreach ($whereMap as $key => $config) {
            // 跳过未配置数据库字段的项
            if (empty($config['field'])) continue;

            // 检查输入值是否存在（跳过空值/空数组）
            $value = $input[$key] ?? null;
            if ($value === '' || $value === []) continue;

            // 构建标准化条件数组
            $conditions[$key] = [
                'search' => $config['search'] ?? 'where',  // 默认使用where查询
                'field' => $config['field'],               // 数据库实际字段
                'operator' => $config['operator'] ?? '=',  // 默认等于操作符
                'value' => $value,                         // 输入值
            ];
        }
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * 获取分页查询结果（核心查询方法）
     * @param array $joins 联表配置（格式：[['table' => 表名, 'conditions' => 关联条件, 'type' => 联表类型]]）
     * @param array $where 额外WHERE条件（直接传递给查询构建器）
     * @return array 分页结果数组（包含total/size/current/list）
     */
    public function getPaginateResults(array $joins = [], array $where = []): array
    {
        // 构建基础查询（处理表别名和联表）
        $query = $this->buildBaseQuery($joins);

        // 应用查询字段（select语句）
        $this->applyFields($query);

        // 应用转换后的条件（where/whereIn等）
        $this->applyWhereConditions($query);

        // 应用额外WHERE条件
        if (!empty($where)) $query->where($where);

        // 应用排序规则
        if (!empty($this->orderBy)) {
            foreach ($this->orderBy as $field => $direction) {
                $query->orderBy($field, $direction);
            }
        }

        // 应用分组
        if (!empty($this->group)) $query->groupBy($this->group);

        // 执行分页查询（使用当前页和每页数量）
        $paginator = $query->paginate($this->size, $this->fields, 'page', $this->current);

        // 转换结果为数组并返回分页结构
        $results = CommonService::convertToArray($paginator->items());
        return [
            'total' => $paginator->total(),
            'size' => $this->size,
            'current' => $this->current,
            'list' => $results,
        ];
    }

    /**
     * 获取单条记录（返回第一条匹配结果）
     * @param array $joins 联表配置（同getPaginateResults）
     * @param array $where 额外WHERE条件（同getPaginateResults）
     * @return array 单条记录数组（无结果返回空数组）
     */
    public function getSingleRecord(array $joins = [], array $where = []): array
    {
        // 构建基础查询并应用配置（字段/条件/排序/分组）
        $query = $this->buildBaseQuery($joins);
        $this->applyFields($query);
        $this->applyWhereConditions($query);
        if (!empty($where)) $query->where($where);
        if (!empty($this->orderBy)) {
            foreach ($this->orderBy as $field => $direction) {
                $query->orderBy($field, $direction);
            }
        }
        if (!empty($this->group)) $query->groupBy($this->group);

        // 获取第一条记录并转换为数组
        $results = $query->first();
        return CommonService::convertToArray($results);
    }

    /**
     * 获取多条记录（返回所有匹配结果，不分页）
     * @param array $joins 联表配置（同getPaginateResults）
     * @param array $where 额外WHERE条件（同getPaginateResults）
     * @return array 多条记录数组（无结果返回空数组）
     */
    public function getMultipleRecord(array $joins = [], array $where = []): array
    {
        // 构建基础查询并应用配置（字段/条件/排序/分组）
        $query = $this->buildBaseQuery($joins);
        $this->applyFields($query);
        $this->applyWhereConditions($query);
        if (!empty($where)) $query->where($where);
        if (!empty($this->orderBy)) {
            foreach ($this->orderBy as $field => $direction) {
                $query->orderBy($field, $direction);
            }
        }
        if (!empty($this->group)) $query->groupBy($this->group);

        // 获取所有记录并转换为数组
        $results = $query->get();
        return CommonService::convertToArray($results);
    }

    /**
     * 构建基础查询构建器（处理表别名和联表）
     * @param array $joins 联表配置数组（格式同getPaginateResults）
     * @return Builder 查询构建器实例
     */
    protected function buildBaseQuery(array $joins): Builder
    {
        $table = $this->getTable();  // 获取模型对应的数据库表名
        $alias = $this->alias;       // 获取设置的表别名

        // 拼接表名（带别名或原始表名）
        $tableName = $alias ? "{$table} as {$alias}" : $table;
        $query = DB::table($tableName);

        // 处理联表操作（支持多种联表类型）
        if (!empty($joins)) {
            foreach ($joins as $join) {
                $table = $join['table'];
                $conditions = $join['conditions'];
                $type = $join['type'];  // 联表类型（如innerJoin、leftJoin）
                $query->join($table, function ($join) use ($conditions) {
                    foreach ($conditions as $condition) {
                        $join->on($condition['first'], $condition['operator'], $condition['second']);
                    }
                }, null, null, $type);
            }
        }

        return $query;
    }

    /**
     * 应用查询字段到构建器（处理select语句）
     * @param Builder $query 查询构建器实例
     */
    protected function applyFields(Builder $query): void
    {
        if (empty($this->fields)) return;  // 无字段配置时跳过

        // 数组格式使用select，字符串格式使用selectRaw（支持SQL表达式）
        if (is_array($this->fields)) {
            $query->select($this->fields);
        } else {
            $query->selectRaw($this->fields);
        }
    }

    /**
     * 应用转换后的查询条件到构建器（处理where/whereIn等多种查询类型）
     * @param Builder $query 查询构建器实例
     */
    protected function applyWhereConditions(Builder $query): void
    {
        foreach ($this->conditions as $condition) {
            // 跳过不完整的条件配置
            if (empty($condition['search']) || empty($condition['field']) || !isset($condition['value'])) {
                continue;
            }

            $value = $condition['value'];
            $field = $condition['field'];
            $operator = $condition['operator'] ?? '=';

            // 根据搜索类型应用不同的查询方法
            switch ($condition['search']) {
                case 'where':
                    // 数组值使用whereIn，否则使用普通where
                    is_array($value) ? $query->whereIn($field, $value) : $query->where($field, $operator, $value);
                    break;
                case 'whereIn':
                    is_array($value) && $query->whereIn($field, $value);
                    break;
                case 'whereNotIn':
                    is_array($value) && $query->whereNotIn($field, $value);
                    break;
                case 'orWhere':
                    $query->orWhere($field, $operator, $value);
                    break;
                case 'whereBetween':
                    is_array($value) && count($value) === 2 && $query->whereBetween($field, $value);
                    break;
                case 'whereColumn':
                    $query->whereColumn($field, $operator, $value);
                    break;
                case 'whereNull':
                    $query->whereNull($field);
                    break;
                case 'whereNotNull':
                    $query->whereNotNull($field);
                    break;
                case 'whereRaw':
                    $query->whereRaw($value);
                    break;
                case 'like':
                    $this->applyLikeCondition($query, $field, $value, $operator);
                    break;
            }
        }
    }

    /**
     * 处理LIKE查询条件（支持前/后/全模糊匹配）
     * @param Builder $query 查询构建器实例
     * @param string $field 数据库字段名
     * @param mixed $value 匹配值
     * @param string $operator 模糊类型（like/like_after/like_before）
     */
    protected function applyLikeCondition(Builder $query, string $field, $value, string $operator): void
    {
        // 定义模糊匹配模式映射（like_after: 后缀模糊，like_before: 前缀模糊，默认全模糊）
        $map = [
            'like_after' => "{$value}%",
            'like_before' => "%{$value}",
            'like' => "%{$value}%",
        ];
        $pattern = $map[$operator] ?? $map['like'];  // 未匹配时使用全模糊
        $query->where($field, 'like', $pattern);
    }
}