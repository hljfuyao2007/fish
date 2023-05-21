<?php

namespace app\admin\controller\fish;

use app\admin\model\FishType;
use app\admin\model\FishUser;
use app\admin\model\SystemAdmin;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * @ControllerAnnotation(title="fish_follow")
 */
class Follow extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\FishFollow();
        
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            list($page, $limit, $where) = $this->buildTableParames();
            $param=$this->request->param();

            $count = $this->model
                ->where($where);
            if(!empty($param["user_id"]) && is_numeric($param["user_id"])){

                $count=$count->where("user_id","=",$param["user_id"]);
            }
            $count=$count->count();
            $list = $this->model
                ->with(['type','user','admin'])
                ->where($where)
                ->page($page, $limit)
                ->order($this->sort);
            if(!empty($param["user_id"]) && is_numeric($param["user_id"])){
                $list=$list->where("user_id","=",$param["user_id"]);
            }
            $list=$list->select();
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
            ];
            return json($data);
        }
        return $this->fetch();
    }

    public function add()
    {
        $param=$this->request->param();
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [];
            $this->validate($post, $rule);
            $type_id=$post["type_id"];
            $typeInfo=FishType::where("id",$type_id)->find();
            $post["status"]=$typeInfo["status"];
            $user=[
                "status"=>$typeInfo["status"],
                "star"=>$typeInfo["star"],
                "type_id"=>$type_id
            ];
            $admin=session('admin');
            $post["admin_id"]=$admin["id"];
            try {
                (new FishUser())->where("id",$post["user_id"])->save($user);
                $save = $this->model->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败:'.$e->getMessage());
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        if(!isset($param["id"])){
            header("location:/");
            exit;
        }
        $username=FishUser::where("id",$param["id"])->field("id,nick_name")->find();
        $this->assign("userinfo",$username);
        return $this->fetch();
    }


    public function edit($id)
    {
        $row = $this->model->find($id);

        empty($row) && $this->error('数据不存在');
        if ($this->request->isPost()) {
//            $post = $this->request->post();
//            $rule = [];
//            $this->validate($post, $rule);
//            try {
//                $save = $row->save($post);
//            } catch (\Exception $e) {
//                $this->error('保存失败');
//            }
//            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $admin=SystemAdmin::where("id",$row["admin_id"])->find();
        $row["admin"]=$admin;
        $this->assign('row', $row);
        $username=FishUser::where("id",$row["user_id"])->field("id,nick_name")->find();
        $this->assign("userinfo",$username);
        return $this->fetch();
    }
    
}