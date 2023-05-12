<?php
declare (strict_types = 1);

namespace app\api\controller\applet;


use app\common\controller\ApiController;
use app\admin\model\ArticleType;
use app\admin\model\Article;
use app\admin\model\Image;
use app\admin\model\LetUser;
use app\admin\model\LetNotice;
use app\admin\model\LetGoods;
use app\admin\model\LetCate;
use app\admin\model\LetImagetype;
use app\admin\model\LetImage;
class News extends ApiController
{

	public function test()
	{
        echo "aaa";exit;
	}
    public function news_list()
    {
        $param = $this->request->param();
        $size=$param["size"]??10;
        $page=$param["page"]??1;
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
        $res=$res->paginate(["page"=>$page,"list_rows"=>$size])->each(function($e){
            $e->content=mb_substr(strip_tags(html_entity_decode($e->content)), 0, 25 , 'utf-8');

            $e->article_type=1;
            if(empty($e->img)) {
                $e->img="http://interface.hrbhuayan.com/upload/20230510/b373ac707b4958d81ebc69c9db00b2a8.jpg";
            }
        });
        return $this->api_data('SUCCESS', $res);
    }
    public function get_type_name(){
        $res=(new ArticleType)
            ->field("id,title,title as name,create_time")
            ->order("sort desc,id desc")
            ->where("id","<",9)
            ->select();
        return $this->api_data('SUCCESS', $res);
    }

    public function get_detail()
    {

        $param = $this->request->param();
        $id=$param["id"]??1;
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
        $res->img="http://interface.hrbhuayan.com/upload/20230510/b373ac707b4958d81ebc69c9db00b2a8.jpg";
        return $this->api_data('SUCCESS', $res);
    }


}
