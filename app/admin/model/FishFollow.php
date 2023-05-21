<?php

namespace app\admin\model;

use app\common\model\TimeModel;

class FishFollow extends TimeModel
{

    protected $name = "fish_follow";

    protected $deleteTime = "delete_time";

    public function user()
    {
        return $this->belongsTo('app\admin\model\FishUser', 'user_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo('app\admin\model\FishType', 'type_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo('app\admin\model\SystemAdmin', 'admin_id', 'id');
    }

    

}