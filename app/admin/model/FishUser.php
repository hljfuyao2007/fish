<?php

namespace app\admin\model;

use app\common\model\TimeModel;

class FishUser extends TimeModel
{

    protected $name = "fish_user";

    protected $deleteTime = "delete_time";

    public function type()
    {
        return $this->belongsTo('app\admin\model\FishType', 'type_id', 'id');
    }

    public function platform()
    {
        return $this->belongsTo('app\admin\model\FishPlatform', 'platform_id', 'id');
    }
    public function admin()
    {
        return $this->belongsTo('app\admin\model\SystemAdmin', 'admin_id', 'id');
    }

    public function system_admin()
    {
        return $this->belongsTo('app\admin\model\SystemAdmin', 'seek_admin_id', 'id');
    }


}