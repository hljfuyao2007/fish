<?php

namespace app\admin\model;

use app\common\model\TimeModel;

class LetUser extends TimeModel
{

    protected $name = "let_user";

    protected $deleteTime = "delete_time";



    // 检查用户和手机号是否对应
    public function check_user($user_id, $phone)
    {
        return $this
            ->where([
                'id'=> $user_id,
                'phone'=> $phone,
            ])
            ->find();
    }

    public function info($user_id)
    {
        return $this
            ->where([
                'id'=> $user_id
            ])
            ->withoutField(['password'])
            ->find();
    }

    /**
     * 手机号登录
     * @param $phone
     * @return AppUser|array|mixed|\think\Model|null
     */
    public function login_phone($phone)
    {
        return $this
            ->where([
                'phone'=> $phone
            ])
            ->field($this->common_field)
            ->find();
    }

    /**
     * @param $account
     * @param $password
     */
    public function login_password($account, $password)
    {
        return $this
            ->where([
                'phone'=> $account,
                'password'=> $password,
            ])
            ->field($this->common_field)
            ->find();
    }

    public function login_wechat($openid, $platform = 1)
    {
        $where = $platform  == 1 ? ['openid'=> $openid] : ['openid_app'=> $openid];
        return $this
            ->where($where)
            ->field($this->common_field)
            ->find();
    }

    public function login_union_id($union_id)
    {
        return $this
            ->where([
                'unionid'=> $union_id,
            ])
            ->field($this->common_field)
            ->find();
    }

}