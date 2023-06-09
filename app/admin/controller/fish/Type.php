<?php

namespace app\admin\controller\fish;

use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * @ControllerAnnotation(title="fish_type")
 */
class Type extends AdminController
{

    use \app\admin\traits\Curd;
    public $sort = [
        'sort' => 'desc',
        'id' => 'desc',
    ];

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\FishType();
        
    }

    
}