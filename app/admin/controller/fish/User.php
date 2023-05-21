<?php

namespace app\admin\controller\fish;

use app\admin\model\FishIllness;
use app\admin\model\FishPlatform;
use app\admin\model\FishType;
use app\admin\model\FishUser;
use app\admin\model\SystemAdmin;
use app\admin\service\AutoSeek;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use think\facade\Cache;

/**
 * @ControllerAnnotation(title="fish_user")
 */
class User extends AdminController
{
    use \app\admin\traits\Curd;
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new \app\admin\model\FishUser();
        
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            $admin=session('admin');
            $auth_ids=explode(",",$admin["auth_ids"]);
            $b1=0;
            $b2=0;
            if(in_array(8,$auth_ids) || $admin["id"] == 1){
                $b1=1;
            }
            if(in_array(9,$auth_ids) || in_array(10,$auth_ids) || $admin["id"] == 1){
                $b2=1;
            }

            list($page, $limit, $where) = $this->buildTableParames();
            if(in_array(9,$auth_ids)){
                $where[]=["seek_admin_id",'=',$admin["id"]];
            }
            $count = $this->model
                ->where($where)
                ->count();
            $list = $this->model
                ->with(['type','platform','admin'])
                ->where($where)
                ->page($page, $limit)
                ->order($this->sort)
                ->select()->each(function($e){
                    $e->seek_admin=SystemAdmin::where("id",$e->seek_admin_id)->field("id,username")->find();
                    if(!$e->seek_admin){
                        $e->seek_admin=["id"=>0,"username"=>"未分配"];
                    }
                });
            $l=[];
            foreach ($list as $v){
                $v["b1"]=$b1;
                $v["b2"]=$b2;
                $l[]=$v;
            }
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $l,
            ];
            return json($data);
        }
        $FishType=new FishType();
        $FishPlatform=new FishPlatform();
        $type=$FishType->field("id,title,color")->column("title","id");
        $platform=$FishPlatform->field("id,name")->column("name","id");
        echo "<script>var typeList=".json_encode($type).";var platformList=".json_encode($platform).";</script>";
        return $this->fetch();
    }

    public function edit2($id)
    {
        $row = $this->model->find($id);
        empty($row) && $this->error('数据不存在');
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [];
            $this->validate($post, $rule);
            if(!empty($post["illness"])) {
                $post["illness"] = implode(',', $post["illness"]);
            }else{
                $post["illness"]="";
            }
            try {
                $save = $row->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败');
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $this->assign('row', $row);
        $row["illness_arr"]=explode(",",$row["illness"]);
        $illness=(new FishIllness)->column("name","id");
        $this->assign("illness_list",$illness);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="添加")
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();

            if(!empty($post["illness"])) {
                $post["illness"] = implode(',', $post["illness"]);
            }else{
                $post["illness"]="";
            }
            $post["admin_id"]=session("admin")["id"];
            $rule = [];
            $this->validate($post, $rule);
            try {
                $seek_admin_id=(new AutoSeek())->auto_seek();
                if($seek_admin_id){
                    $post["seek_admin_id"]=$seek_admin_id;
                    Cache::set("new_".$seek_admin_id,1);
//                    $this->auto_seek_go();//查看是否有为被分配的人，分配给上线的人
                }
                $save = $this->model->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败:'.$e->getMessage());
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $illness=(new FishIllness)->column("name","id");
        $this->assign("illness_list",$illness);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="编辑")
     */
    public function edit($id)
    {
        $row = $this->model->find($id);
        $row["illness_arr"]=explode(",",$row["illness"]);
        empty($row) && $this->error('数据不存在');
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if(!empty($post["illness"])) {
                $post["illness"] = implode(',', $post["illness"]);
            }else{
                $post["illness"]="";
            }
            $rule = [];
            $this->validate($post, $rule);
            try {
                $save = $row->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败');
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $this->assign('row', $row);
        $illness=(new FishIllness)->column("name","id");
        $this->assign("illness_list",$illness);
        return $this->fetch();
    }


    /**
     * @NodeAnotation(title="分配人员")
     */
    public function fp()
    {
        $param=$this->request->param();
        if(empty($param["id"])){
            return "error";
        }
        if ($this->request->isPost()) {
            $seek_admin_id=$param["admin_id"];
            $ids=explode(",",$param["id"]);
            foreach ($ids as $id) {
                $save=FishUser::where("id",$id)->save(["seek_admin_id"=>$seek_admin_id]);
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }

//        $admin_list=SystemAdmin::where("auth_ids","REGEXP","(^|,)9($|,)")->select();
        $admin_list=SystemAdmin::where("auth_ids REGEXP '(^|,)(9|10)($|,)'")->select();
        $this->assign("admin_list",$admin_list);
        return $this->fetch();
    }

//    public function auto_seek_go(){
//        //自动分配未被分配的
//        $last_user=FishUser::field("id,seek_admin_id")->where("seek_admin_id IS NUll")->select();
//        foreach ($last_user as $v){
//            $seek_admin_id=$this->auto_seek();
//            if($seek_admin_id){
//                Cache::set("new_".$seek_admin_id,1);
//                FishUser::where("id",$v["id"])->update(["seek_admin_id"=>$seek_admin_id]);
//            }
//        }
//    }
//
//    /*自动分配咨询*/
//    public function auto_seek(){
//        /*查询所有咨询人员*/
//        $admin_all=SystemAdmin::where("auth_ids REGEXP '(^|,)(9|10)($|,)'")->select();
//        $admin_login=Cache::get("login_admin");
//        $now=time();$admin_array=[];
//        foreach ($admin_all as $key=>$value){
//          $keep=$admin_login[$value["id"]]??0;
//            if($keep>($now-300)){
//                //超过五分钟未获取登陆状态视为离线
//                $admin_array[]=$value["id"];
//            }
//        }
//        if(empty($admin_array)){
//            //无人在线,返回false
//            return false;
//        }
//        //根据算法，算出最空闲的人
//        $admin_id=$this->get_last_admin($admin_array);
//
//        return $admin_id;
//    }
//
//    public function get_last_admin($admin_array){
//        //排除算法，从后向前剔除数组内的人，直至剩下最后一个
//        $last_fish_array=Cache::get("last_fish_array");
//        if(!is_array($last_fish_array)){$last_fish_array=[];}
//        $last_fish_array=array_reverse($last_fish_array);
//        foreach ($last_fish_array as $v){
//            if(in_array($v,$admin_array)){
//                $admin_array=array_diff($admin_array, [$v]);
//                //删除数组后，如果数组空，则数据为最后一条，直接返回
//                if(empty($admin_array)){
//                    //$admin_id 存入接客名单，下次优先排除
//                    $last_fish_array[]=$v;
//                    if(count($last_fish_array)>20){
//                        //接客名单维持在20个就好，多了没用
//                        array_shift($last_fish_array);
//                    }
//                    Cache::set("last_fish_array",$last_fish_array);
//                    return $v;
//                }
//            }
//        }
//        //排出后，数组内还有值，则随机指定
//        $res=array_rand($admin_array);
//        //$admin_id 存入接客名单，下次优先排除
//        $last_fish_array[]= $admin_array[$res];
//        if(count($last_fish_array)>20){
//            //接客名单维持在20个就好，多了没用
//            array_shift($last_fish_array);
//        }
//        Cache::set("last_fish_array",$last_fish_array);
//        return $admin_array[$res];
//    }
    
}