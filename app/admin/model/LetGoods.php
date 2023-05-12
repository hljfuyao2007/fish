<?php

namespace app\admin\model;

use app\common\model\TimeModel;

class LetGoods extends TimeModel
{

    protected $name = "let_goods";

    protected $deleteTime = "delete_time";


    public function cate()
    {
        return $this->belongsTo('app\admin\model\LetCate', 'cate_id', 'id');
    }

}