<?php
declare(strict_types=1);
namespace Sunsgne\WebmanSmsRegister\Traits;


use support\Redis;

trait OrmCurdTrait
{

    /** @var int  */
    public static int $_DEFAULT_SIZE = 10;

    /** @var int  */
    public static int $_DEFAULT_PAGE = 1;

    /**
     * @param array $where 条件
     * @param array $forPage 分页数组[size=>1,page=2]
     * @param array $filed 查询字段，选填
     * @param array $orderBy 排序[id=desc,name=asc]
     * @param array $whereIn whereIn [a=[1,2] , b=[3,4]]
     * @param array $with 关联查询
     * @param array $groupBy 分组
     * @return array ['list'=>[], 'total' => 0]
     * @Time 2023/7/19 17:19
     * @author sunsgne
     */
    public static function getByWhereForPage(
        array $where,
        array $forPage,
        array $filed = ['*'],
        array $orderBy = [],
        array $whereIn = [],
        array $with = [],
        array $groupBy = [],
    ): array
    {
        $query = self::query()->where($where);
        if (!empty($with)) {
            $query->with($with);
        }
        if (!empty($orderBy)) {
            foreach ($orderBy as $k => $v) {
                $query->orderBy($k, $v);
            }
        }
        if (!empty($whereIn)) {
            foreach ($whereIn as $v) {
                $query->whereIn($v['key'], $v['value']);
            }
        }
        if (!empty($groupBy)) {
            $query->groupBy($groupBy);
        }

        $model = $query->select($filed)->paginate(
            isset($forPage['size']) ? (int)$forPage['size'] : DEFAULT_PAGE_SIZE,
            $filed,
            null,
            isset($forPage['page']) ? (int)$forPage['page'] : DEFAULT_PAGE
        );
        return ['list' => $model->items(), 'total' => $model->total()];
    }

    /**
     * @param array $where 条件
     * @param array $forPage 分页数组[size=>1,page=2]
     * @param array $filed 查询字段，选填
     * @param array $orderBy 排序[id=desc,name=asc]
     * @param array $whereIn whereIn [a=[1,2] , b=[3,4]]
     * @param array $with 关联查询
     * @param array $groupBy 分组
     * @return array [...]
     * @Time 2023/7/19 17:19
     * @author sunsgne
     */
    public static function getItemByWhereForPage(
        array $where,
        array $forPage,
        array $filed = ['*'],
        array $orderBy = [],
        array $whereIn = [],
        array $with = [],
        array $groupBy = [],
    ): array
    {
        $query = self::query()->where($where);
        if (!empty($with)) {
            $query->with($with);
        }
        if (!empty($orderBy)) {
            foreach ($orderBy as $k => $v) {
                $query->orderBy($k, $v);
            }
        }
        if (!empty($whereIn)) {
            foreach ($whereIn as $v) {
                $query->whereIn($v['key'], $v['value']);
            }
        }
        if (!empty($groupBy)) {
            $query->groupBy($groupBy);
        }
        $model = $query->select($filed)->forPage(
            page: $forPage['page'] ?? self::$_DEFAULT_PAGE ,
            perPage: $forPage['size'] ?? self::$_DEFAULT_SIZE
        )->get();

        return $model->isNotEmpty() ? $model->toArray() : [];
    }

    /**
     * 获取模型中所有满足条件的数据
     * @param array $where ["field"=>"1"]  or [ ["field",">","4"] ]
     * @param array $filed ["*"]
     * @param array $orderBy ["field" => "desc"]
     * @return array
     * @Time 2023/7/20 18:04
     * @author sunsgne
     */
    public static function getAllByWhere(
        array $where,
        array $filed = ['*'],
        array $orderBy = []
    ): array
    {
        $query = self::query()->where($where)->select($filed);
        if (!empty($orderBy)) {
            foreach ($orderBy as $k => $v) {
                $query->orderBy($k, $v);
            }
        }
        $query = $query->get();
        return $query->isNotEmpty() ? $query->toArray() : [];
    }


    /**
     * @param array $where
     * @param array $filed
     * @return array
     * @Time 2023/7/19 17:24
     * @author sunsgne
     */
    public static function firstByWhere(
        array $where,
        array $filed = ['*'],
    ): array
    {
        $query = self::query()->where($where)->select($filed)->first();

        return $query ? $query->toArray() : [];
    }

    /**
     * @param array $where
     * @param array $filed
     * @return array|mixed ['id'=>'']
     * @Time 2023/7/19 17:37
     * @author sunsgne
     */
    public static function firstByWhereForCache(
        array $where,
        array $filed = ['*']): mixed
    {
        $cacheKey = self::class . ':' . md5(json_encode($where));
        $cache    = Redis::get($cacheKey);
        $data     = $cache ? json_decode($cache, true) : [];
        if (empty($cache) and empty($data)) {
            $query = self::query()->where($where)->select($filed)->first();
            if ($query and $data = $query->toArray()) {
                Redis::setEx($cacheKey, DEFAULT_CACHE_TTL, json_encode($data));
                return $data;
            }
            return [];
        }
        return $data;
    }


    /**
     * 根据条件更新数据
     * @param array $where
     * @param array $updateData
     * @return bool
     * @Time 2023/9/26 13:40
     * @author sunsgne
     */
    public static function updateByWhere(array $where , array $updateData): bool
    {
        if (empty($where) or empty($updateData)){
            return false;
        }
        return (bool)self::query()->where($where)->update($updateData);
    }
}