<?php
declare (strict_types = 1);

namespace app\api\controller\applet;


use app\admin\model\LetVideo;
use app\common\controller\ApiController;
use app\extra\QrCode;
use think\response\Json;

class Video extends ApiController
{

	public function test()
	{
		return 'ok111';
	}
	public function list(): Json
    {
        $param = $this->request->param();
        $size=$param["size"]??10;
        $get_page=(int) $param["page"]??1;
        $res=(new LetVideo)
            ->field("id,title,image,video,create_time")
            ->order("id desc");
        $res=$res->page($get_page,8)
            ->select();;
        return $this->api_data('SUCCESS', $res);
    }

    public function content(): Json
    {
        $param = $this->request->param();
        $id=$param["id"]??3;
        $res=(new LetVideo)->where("id",$id)->find();
        $res["content"]=htmlspecialchars_decode($res["content"]);
        return $this->api_data('SUCCESS', $res);
    }


}
