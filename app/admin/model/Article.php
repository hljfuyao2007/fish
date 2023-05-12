<?php

namespace app\admin\model;

use app\common\model\TimeModel;

class Article extends TimeModel
{

    protected $name = "article";

    protected $deleteTime = "delete_time";


}