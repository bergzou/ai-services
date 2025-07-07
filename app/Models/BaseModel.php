<?php
/**
 * @Notes: 基础模型类
 * @Date: 2024/3/27
 * @Time: 10:10
 * @Interface BaseModel
 * @return
 */

namespace App\Models;


use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;



class BaseModel extends Model
{
    protected  $alias;

    public function setAlias($alias): string
    {
        return $this->alias = $alias;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Notes: 获取列表分页数据或多条
     * Date: 2024/3/29 14:35
     * @param array $where
     * @param $fields
     * @param array $orderBy
     * @param null $group
     * @param array $joins
     * @param bool $is_count
     * @param int $current
     * @param int $size
     * @param bool $is_distinct
     * @return array
     */
    public function getQueryByCondition( array $where = [], $fields, array $orderBy = [],  bool $is_count = true, int $current = 1, int $size = 50,  $group = null , array $joins = [] , bool $is_distinct = false ): array
    {




        if( !empty($joins) && !empty( $this->getAlias() )){
            $query = DB::table($this->getTable().' as '. $this->getAlias() ); // 使用模型的表名
            foreach ($joins as $join) {

                $table = $join['table'] ;
                $conditions = $join['conditions'];
                $type = $join['type'];
                $query->join($table, function ($join) use ($conditions) {
                    foreach ($conditions as $condition) {
                        $join->on($condition['first'], $condition['operator'], $condition['second']);
                    }
                }, null, null, $type);
            }

        }else{
            $query = DB::table($this->getTable()); // 使用模型的表名
        }

        if ($is_distinct) {
            $query = $query->distinct();
        }


        // 选择要查询的字段
        if (!empty($fields)) {
            if(is_array($fields)){
                $query->select($fields);
            }else{
                $query->selectRaw($fields);
            }
        }


        // 条件
        if ( !empty( $where )) {
            $query = $this->getWhereData($where,$query);
        }



        if (!empty($group)) {
            $query->groupBy($group);
        }


        // 添加排序
        if (!empty($orderBy)) {
            foreach ($orderBy as $field => $direction) {
                $query->orderBy($field, $direction);
            }
        }


        // 添加分页
        if ($is_count) {
            $total = $query->count();
            if ($current > 0 && $size > 0) {
                $offset = ($current - 1) * $size;
                $query->offset($offset)->limit($size);
            }
            $results = $query->get()->toArray();
            $results  = [
                'total' => $total,
                'size' => $size,
                'current' => $current,
                'list' => $results
            ];

        } else{
            if ($current > 0 && $size > 0) {
                $offset = ($current - 1) * $size;
                $query->offset($offset)->limit($size);
            }
            $results = $query->get()->toArray();
        }
        return $results;

    }

    /**
     * @param array $where
     * @param array $joins
     * @param bool $is_distinct
     * @return int
     */
    public function getQueryByCount( array $where = [] , array $joins = [] ,$is_distinct = false)
    {

        $query = DB::table($this->getTable().' as '. $this->getAlias() ); // 使用模型的表名

        if( !empty($joins) && !empty( $this->getAlias() )){
            foreach ($joins as $join) {

                $table = $join['table'] ;
                $conditions = $join['conditions'];
                $type = $join['type'];
                $query->join($table, function ($join) use ($conditions) {
                    foreach ($conditions as $condition) {
                        $join->on($condition['first'], $condition['operator'], $condition['second']);
                    }
                }, null, null, $type);
            }

        }
        if ($is_distinct){
            $query = $query->distinct();
        }
        // 条件
        if ( !empty( $where )) {
            $query = $this->getWhereData($where,$query);
        }

        return $query->count( $this->getAlias().'.id');

    }


    /** where 映射
     * @param array $input
     * @param array $whereMap
     * @param bool $keep
     * @param array $convertKeys
     * @return array
     */
    public function convertConditions($input = [], $whereMap = [], $convertKeys = []): array
    {
        // 初始化参数
        $where = [];
        if ( empty($input) ) {
            return $where;
        }
        $inputKeys = array_keys($input);

        foreach ($whereMap as $key => $row) {
            $key = trim($key);
            if(!empty($convertKeys) && count($convertKeys) > 0 ){
                if (!in_array($key, $inputKeys) && !in_array($key, $convertKeys)) {
                    continue; // 忽略不在配置项的字段
                }
            }

            if ( !isset($input[$key]) || $input[$key] === ''  || $input[$key] === []){
                continue; // 忽略值 不存在 空字符串 空数组
            }

            if ( empty($row['search']) ){
                $row['search'] = 'where';  // 默认 where
            }

            if ( empty($row['field']) ){
                continue; // 未配置 查询操作 忽略
            }

            if ( empty($row['operator']) ){
                $row['operator'] = '=' ; // 默认查询关系
            }

            if (!in_array($key, $inputKeys)) {
                continue; // 忽略不在配置项的字段
            }

            $where[$key] = [
                'search' => $row['search'],
                'field' => $row['field'],
                'operator' => $row['operator'],
                'value' => $input[$key],
            ];
        }

        return $where;
    }



    /** 获取where
     * @param array $where
     * @param Builder|null $builder
     * @return Builder|null
     */
    public function getWhereData(array $where = [], Builder $builder = null){

        if (empty($where)){
            return $builder;
        }

        foreach ( $where as  $fieldVal ){
            if (empty($fieldVal['search']) || empty($fieldVal['field']) || $fieldVal['value'] === [] || $fieldVal['value'] === ''){
                continue ;
            }
            switch ($fieldVal['search']){
                case 'where':
                    if (is_array($fieldVal['value'])){
                        $builder->whereIn($fieldVal['field'] , $fieldVal['value']);
                    }else {
                        $builder->where($fieldVal['field'], $fieldVal['operator'], $fieldVal['value']);
                    }

                    break;
                case 'whereIn':
                    if ( !is_array($fieldVal['value'])) break;
                    $builder->whereIn($fieldVal['field'] , $fieldVal['value']);
                    break;
                case 'whereNotIn':
                    if ( !is_array($fieldVal['value'])) break;
                    $builder->whereNotIn($fieldVal['field'] , $fieldVal['value']);
                    break;
                case 'orWhere':
                    $builder->orWhere($fieldVal['field'] , $fieldVal['operator'] , $fieldVal['value']);
                    break;
                case 'whereBetween':
                    if ( !is_array($fieldVal['value'])) break;
                    $builder->whereBetween($fieldVal['field'] , $fieldVal['value']);
                    break;
                case 'whereNot':
                    $builder->where($fieldVal['field'] , $fieldVal['operator'] , $fieldVal['value']);
                    break;
                case 'whereColumn':
                    $builder->whereColumn($fieldVal['field'] , $fieldVal['operator'],$fieldVal['value']);
                    break;
                case 'whereNull':
                    $builder->whereNull($fieldVal['field']);
                    break;
                case 'whereNotNull':
                    $builder->whereNotNull($fieldVal['field']);
                    break;
                case 'whereRaw':
                    $builder->whereRaw($fieldVal['value']);
                    break;
                case 'like':

                    if ($fieldVal['operator'] == 'like') {
                        $builder->where($fieldVal['field'], 'like', $fieldVal['value']);

                    } elseif ($fieldVal['operator'] == 'like_after') {
                        $builder->where($fieldVal['field'], 'like', '%' . $fieldVal['value']);

                    } elseif ($fieldVal['operator'] == 'like_before') {
                        $builder->where($fieldVal['field'], 'like', $fieldVal['value'] . '%');

                    }else{
                        $builder->where($fieldVal['field'], 'like', '%' . $fieldVal['value'] . '%');

                    }
                    break;
                default:
                    break;
            }
        }

        return $builder;
    }

    /**
     * @param $id
     * @param $field
     * @param $isObj //是否返回对象数据格式
     * @return array|Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function forUpdateByPk($id,$field = '*',$isObj=true){
        if(!is_array($field) && strstr($field,',')){
            $field = explode(',',$field);
        }
        $first = DB::table($this->table)->where('id',$id)->select($field)->lockForUpdate()->first();
        if ($isObj || empty($first)) return $first;

        return (array) $first;
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');//解决模型输出时间错误
    }

    public function __call($method, $parameters)
    {
        if(preg_match('/^get(.+)NameAttribute$/',$method,$matches)){
            return $this->getEnumAttributeName($matches[1]);//默认属性获取方法(append)
        }
        return parent::__call($method, $parameters); // TODO: Change the autogenerated stub
    }

    protected function getEnumAttributeName($attribute)
    {
        $funcName = 'get' . $attribute . 'Map';
        $field    = \Str::snake($attribute, '_');
        if (empty($this->enumClass)) {
            //如果模型未设置对应的枚举类,将尝试自动匹配
            $classStr        = rtrim(static::class, 'Model');
            $enumClass       = str_replace('Models\\', 'Enums\\Enum', $classStr);
            $this->enumClass = $enumClass;
        }
        if (!class_exists($this->enumClass)) {
            return '';
        }
        $enums = $this->enumClass::{$funcName}();
        if (empty($enums)) {
            return '';
        }
        $val = $this->attributes[$field];
        return $enums[$val] ?? '';
    }
}
