<?php

namespace app\admin\controller;

use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use think\Db;
use app\admin\model\ArticleType;
/**
 * @ControllerAnnotation(title="article")
 */
class Article extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\Article();
        $this->sort="sort desc,id desc";
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            list($page, $limit, $where) = $this->buildTableParames();
            $count = $this->model
                ->where($where)
                ->count();
            $list = $this->model
                ->where($where)
                ->page($page, $limit)
                ->order($this->sort)
                ->select();
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
            ];
            return json($data);
        }
        $type=(new ArticleType)->field("id,title")->column("title","id");
        echo "<script>var typeJson=".json_encode($type)."</script>";
        return $this->fetch();
    }


    public function type(){
        $type=(new ArticleType)->field("id,title")->column("title","id");
        return json($type);
    }
    
}