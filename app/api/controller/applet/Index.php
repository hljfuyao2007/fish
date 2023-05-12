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
class Index extends ApiController
{

	public function test()
	{
        phpinfo();
	}

    public function notice_content()
    {
        $param = $this->request->param();
        if(!isset($param["id"])){
            return $this->api_data(-1,[],"参数错误");
        }
        $id=(int) $param["id"];
        $goodsInfo=(new LetNotice)
            ->where("id",$id)
            ->find();
        $goodsInfo["content"]=htmlspecialchars_decode($goodsInfo["content"]);
        return $this->api_data('SUCCESS', $goodsInfo);
    }

    public function shop_type(): \think\response\Json
    {

        $array=[
            [
                "id"=>1,
                "title"=>"第一分类"
            ],
            [
                "id"=>2,
                "title"=>"第二分类"
            ],

        ];
        return $this->api_data('SUCCESS', $array);
    }
    public function shop_list(): \think\response\Json
    {

        $param = $this->request->param();
        $get_page=(int) $param["page"]??1;
        $group=(int) $param["group"]??0;
        $shopList=(new LetGoods)
            ->field("id,title,logo as img,discount_price as money,market_price as shi_money,remark as text,create_time")
            ->where("status",1)
            ->order("sort desc,id desc");
        if($group>0){
            $shopList=$shopList->where("cate_id",$group);
        }
        $shopList=$shopList
            ->page($get_page,8)
            ->select();
        $type=(new LetCate)->order("sort desc,id asc")->select();
        $type_array=[[
            "id"=>0,
            "title"=>"全部"
        ],];
        foreach ($type as $item){
            $type_array[]=$item;
        }

        return $this->api_data('SUCCESS', ["group"=>$type_array,"shop"=>$shopList]);
    }
    public function goods_info(): \think\response\Json
    {
        $param = $this->request->param();
        if(!isset($param["id"])){
            return $this->api_data(-1,[],"参数错误");
        }
        $id=(int) $param["id"];
        $goodsInfo=(new LetGoods)
            ->where("id",$id)
            ->find();
//        $goodsInfo["images"];
        $goodsInfo["images"]=explode("|",$goodsInfo["images"]);
        $goodsInfo["describe"]=htmlspecialchars_decode($goodsInfo["describe"]);

        return $this->api_data('SUCCESS', $goodsInfo);
    }

    public function getIndex(): \think\response\Json
    {

        $notice =(new letNotice)
            ->field("id,title,create_time")
            ->order("id desc")
            ->select();
        $noticeList=[];
        foreach ($notice as $item) {
            $noticeList[]=$item["title"];
        }
//        $noticeList=$notice;
        $banner=(new LetImage)
            ->field("id,title,image,create_time")
            ->where("type_id",1)
            ->order("id desc")
            ->select();

        $bannerList=[];
        foreach ($banner as $item) {
            $bannerList[]=$item["image"];
        }
        $shopList=(new LetGoods)
            ->field("id,title,logo as img,discount_price as money,market_price as shi_money,remark as text,create_time")
            ->order("sort desc,id desc")
            ->where("status",1)
            ->limit(4)
            ->select();
//            [
//            '滚动通知第一条',
//            '滚动通知第二条',
//            '滚动通知也可以有第三条',
//            '滚动通知可以有很多条',
//        ];
//        $bannerList = ['http://hrbhuayan.com/images/head2.jpg',];
//        $shopList = [
//            [
//                "id"=>1,
//                "title"=>"测试商品",
//                "img"=>"http://hrbhuayan.com/images/img1.jpg",
//                "money"=>"19.99",
//                "shi_money"=>"999.00",
//                "text"=>"我是文字说明"
//            ],
//            [
//                "id"=>2,
//                "title"=>"测试商品2",
//                "img"=>"http://hrbhuayan.com/images/img3.jpg",
//                "money"=>"8.88",
//                "shi_money"=>"888.00",
//                "text"=>"我是文字说明"
//            ],
//        ];
        $video = [
            "url"=>"http://interface.hrbhuayan.com/upload/20230505/36819bacf18612d92e72d9ee62085265.mp4",
            "image"=>"http://interface.hrbhuayan.com/upload/20230505/d78600a3524515fbb6a4f30a3824faee.jpg",
        ];
        $banner2=[
//            "http://hrbhuayan.com/images/img1.jpg",
//            "http://hrbhuayan.com/images/img2.jpg",
//            "http://hrbhuayan.com/images/img3.jpg",
            "http://interface.hrbhuayan.com/static/image/int1.jpg",
            "http://interface.hrbhuayan.com/static/image/int2.jpg",
            "http://interface.hrbhuayan.com/static/image/int3.jpg",
            "http://interface.hrbhuayan.com/static/image/int4.jpg",
            "http://interface.hrbhuayan.com/static/image/int5.jpg",
        ];
        $return=[
            "noticeList"=>$noticeList,
            "bannerList"=>$bannerList,
            "shopList"=>$shopList,
            "video"=>$video,
            "notice"=>$notice,
            "banner2"=>$banner2
        ];
        return $this->api_data('SUCCESS',$return);
    }

}
