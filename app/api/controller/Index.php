<?php
declare (strict_types = 1);

namespace app\api\controller;


use app\admin\model\FishUser;
use app\admin\model\Reservation;
use app\admin\service\AutoSeek;
use app\common\controller\ApiController;
use app\admin\model\ArticleType;
use app\admin\model\Article;
use app\admin\model\Image;
use Swoole\Redis;
use think\facade\Cache;
use think\app;

class Index extends ApiController
{

	public function test()
	{
        //生成二维码

		return 'ok';
	}


    public function get_index_image()
    {
        $res=Image::where("type",1)->select();
        return $this->api_data('SUCCESS', $res);
    }

    public function get_2_image()
    {
        $res=Image::where("type",2)->select();
        return $this->api_data('SUCCESS', $res);
    }

    public function get_article_type()
    {
        $res=(new ArticleType)->order("sort desc,id desc")->select();
        return $this->api_data('SUCCESS', $res);
    }

    public function get_article()
    {
        $param = $this->request->param();
        $type_id=$param["type_id"];
        $size=$param["size"]??10;
        $res=(new Article)->where("type_id",$type_id)->order("sort desc,id desc")->paginate($size);
        return $this->api_data('SUCCESS', $res);
    }

    public function article_list()
    {
        $param = $this->request->param();
        $size=$param["size"]??10;
        $res=(new Article)->order("sort desc,id desc");
        if(isset($param["tj"]) && in_array($param["tj"],[0,1])){
            $res=$res->where("tj",$param["tj"]);
        }
        if(isset($param["rd"]) && in_array($param["rd"],[0,1])){
            $res=$res->where("rd",$param["rd"]);
        }
        if(isset($param["type_id"]) && is_numeric($param["type_id"])){
            $res=$res->where("type_id",$param["type_id"]);
        }
        $res=$res->paginate($size);
        return $this->api_data('SUCCESS', $res);
    }

    public function get_article_list()
    {
        $ArticleType=(new ArticleType)->select();
        $return=[];
        foreach ($ArticleType as $t){
            $res=(new Article)->where("type_id",$t["id"])->order("sort desc,id desc")->limit(10)->select();
            $t["data"]=$res;
            $return[]=$t;
        }

        return $this->api_data('SUCCESS', $return);
    }


    public function get_article_content()
    {
        $param = $this->request->param();
        $id=$param["id"];
        $res=(new Article)->where("id",$id)->find();
        $res["content"]=htmlspecialchars_decode($res["content"]);
        $res["type_name"]=(new ArticleType)->where("id",$res["type_id"])->value("title");

        $last=(new Article)->where("type_id",$res["type_id"])
            ->where("id","<",$res["id"])
            ->order("sort desc,id desc")
            ->limit(1)
            ->find();
        $next=(new Article)->where("type_id",$res["type_id"])
            ->where("id",">",$res["id"])
            ->order("sort asc,id asc")
            ->limit(1)
            ->find();
        if($last){
            $res["last_name"]=$last["title"];
            $res["last_id"]=$last["id"];
        }else{
            $res["last_name"]="没有了";
            $res["last_id"]=0;
        }

        if($next){
            $res["next_name"]=$next["title"];
            $res["next_id"]=$next["id"];
        }else{
            $res["next_name"]="没有了";
            $res["next_id"]=0;
        }
        return $this->api_data('SUCCESS', $res);
    }

    public function get_type_name(){
        $param = $this->request->param();
        $id=$param["id"];
        $type_name=(new ArticleType)->where("id",$id)->value("title");
        return $this->api_data('SUCCESS', $type_name);
    }
    public function ask(){
        $param = $this->request->param();
        $images=$param["images"]??"";
        $content=$param["content"]??"";
        $phone=$param["phone"]??"";
        $name=$param["name"]??"";

        if (!preg_match("/^1[34578]\d{9}$/", $phone)) {
            return $this->api_data(-1, [],"手机号码格式不正确");
        }

        $arr=[
            "images"=>$images,
            "content"=>$content,
            "phone"=>$phone,
            "name"=>$name
        ];
        $type_name=(new Reservation)->create($arr);
        return $this->api_data('SUCCESS', [],"信息提交成功");

    }


    public function keep_login(){
        $param=$this->request->param();
        $admin_id=$param["admin_id"];
        $admin_login=Cache::get("login_admin");
        if(!is_array($admin_login)){$admin_login=[];}
        $admin_login[$admin_id]=time();
        Cache::set("login_admin",$admin_login);
        $new_if=Cache::get("new_".$admin_id);
        if($new_if){
            $new=1;
            Cache::set("new_".$admin_id,0);
        }else{
            $new=0;
        }
//        $app=new App();
//        $user_a=new \app\admin\controller\fish\User($app);
        (new AutoSeek)->auto_seek_go();
        return $this->api_data('SUCCESS', ["new"=>$new],"");
    }


    public function input_sub(){
        //TODO   将下面return 删除  表单就可以提交了
        return $this->api_data('SUCCESS',"");
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $post["admin_id"]=1;
            $notes="疾病：".$post["type2"].PHP_EOL."预约时间：".$post["type2"];
            $array=[
                "admin_id"=>1,
                "nick_name"=>$post["name"],
                "notes"=>$notes,
            ];
            $seek_admin_id=(new AutoSeek())->auto_seek();
            if($seek_admin_id){
                $array["seek_admin_id"]=$seek_admin_id;
                Cache::set("new_".$seek_admin_id,1);
            }
            $save = (new FishUser)->save($array);
            return $this->api_data('SUCCESS',"");
        }else{
            return $this->api_data(-1,[],"错误");
        }
    }

}
