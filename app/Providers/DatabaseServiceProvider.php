<?php

namespace App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        Builder::macro('multiWhere',function($params,$whereMap){

            $configs = [];
            foreach ($whereMap as $key => $item) {
                if(empty($params[$key])){
                    continue;
                }
                $value = $params[$key];
                $field = $item['field'];
                $operator = $item['operator'] ?? '=';
                $configs[] = ['field'=>$field,'operator'=>$operator,'value'=>$value];
            }
            foreach ($configs as $item){
                $operator = $item['operator'];
                switch ($operator) {
                    case '=':
                        if (is_array($item['value'])) {
                            $this->whereIn($item['field'], $item['value']);
                        } else {
                            $this->where($item['field'], $operator, $item['value']);
                        }
                        break;
                    case '!=':
                        $this->where($item['field'], $operator, $item['value']);
                        break;
                    case 'like':
                        $this->where($item['field'], 'like', '%' . $item['value'] . '%');
                        break;
                    case 'left_like':
                        $this->where($item['field'], 'like', '%' . $item['value']);
                        break;
                    case 'right_like':
                        $this->where($item['field'], 'like', $item['value']. '%');
                        break;
                    case 'in':
                        if (!is_array($item['value'])) {
                            $this->where($item['field'], $item['value']);
                        } else {
                            $this->whereIn($item['field'], $item['value']);
                        }
                        break;
                    case 'between':
                        $this->whereBetween($item['field'], $item['value']);
                        break;
                    case 'find_in_set':
                        $this->whereRaw("FIND_IN_SET(?,{$item['field']})",[$item['value']]);
                        break;
                    case 'null':
                        $this->whereNull($item['field']);
                        break;
                    case '>=':
                        $this->where($item['field'], $operator, $item['value']);
                        break;
                    case '>':
                        $this->where($item['field'], $operator, $item['value']);
                        break;
                    case '<=':
                        $this->where($item['field'], $operator, $item['value']);
                        break;
                    case '<':
                        $this->where($item['field'], $operator, $item['value']);
                        break;
                    default:
                        $this->where($item['field'], $item['value']);
                }
            }
            return $this;
        });


        \Illuminate\Database\Eloquent\Builder::macro('paging', function ($page = '', $size = '') {
            $requestParams = Request::all(['size','current']);
            if(empty($page)){
                $page = $requestParams['current'];
            }
            if(empty($size)){
                $size = $requestParams['size'];
            }

            $page = (int)$page;
            if(!$page){
                $page = 1;
            }
            $size = (int)$size;
            if(!$size){
                $size = 20;
            }

            return $this->paginate($size, ['*'], '', $page);
        });


    }
}
