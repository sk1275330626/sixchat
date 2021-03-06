<?php
/***************************************************************************
 *
 * Copyright (c) 2017 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file AuthController.class.php
 * @author 1275330626(com@qq.com)
 * @date 2017/08/06 03:22:39
 * @brief
 *
 **/

namespace Home\Controller;

use Home\Service\UserService;
use Util\ErrCodeUtils;
use Util\ParamsUtils;

class AuthController extends BaseController
{
    /**
     * @brief 登录
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function login()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        if (!empty($params['session_user_name'])) {
            // 判断是否已经登录
            $this->redirect('/moments/index');
            return;
        }

        $idPlaceholder = "Name";
        $passwordPlaceholder = "Password";
        $headImage = "default_head.jpg"; //默认头像

        // 获取cookie
        if (isset($_COOKIE["id"])) {
            $cookieId = $_COOKIE["id"]; // 存放cookie获取的id
        }
        if (isset($_COOKIE["password"])) {
            $cookiePassword = $_COOKIE["password"]; // 存放cookie获取的password
        }

        $id = $params['id'];
        // 为空则读入cookie值
        if (empty($id) && !empty($cookieId)) {
            $id = $cookieId;
        }
        $password = $params['password'];
        if (empty($password) && !empty($cookiePassword)) {
            $password = $cookiePassword;
        }

        // 验证登录
        if (!empty($id) && !empty($password)) {
            $obj = new UserService();
            $ret = $obj->login($id, $password);
            if (ErrCodeUtils::USER_NOT_EXIST === $ret['code']) {
                // 用户不存在
                $id = null;
                $password = null;
                // $idPlaceholder = "该用户不存在";
                $idPlaceholder = $ret['msg'];
            } else if (ErrCodeUtils::ERR_PASSWORD === $ret['code']) {
                // 密码错误
                $password = null;
                // passwordPlaceholder = "密码错误";
                $passwordPlaceholder = $ret['msg'];
            } else if (ErrCodeUtils::SUCCESS === $ret['code']) {
                // 登录成功
                $this->redirect('/moments/index');
                return;
            }
        }

        if (!empty($id)) {
            // 加载用户头像
            $condition['user_name'] = $id;
            $avatar = D('User')->getUserAvatar($condition);
            if ($avatar) {
                $headImage = $avatar;
            }
        }

        $array['head_image'] = $headImage;
        $array['id_placeholder'] = $idPlaceholder;
        $array['password_placeholder'] = $passwordPlaceholder;
        $array['id'] = $id;
        $array['password'] = $password;
        $this->assign($array); // 模板赋值
        $this->display("Login/index"); // 模板渲染
    }

    /**
     * @brief 注销
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function logout()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new UserService();
        $obj->logout();
        header("Location:login");
    }

    /**
     * @brief 注册
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function register()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $id = $params['id'];
        $password = $params['password'];
        $idPlaceholder = "新的账号";
        if (!empty($id) && !empty($password)) {
            $obj = new UserService();
            $ret = $obj->register($id, $password); // 调用注册api
            if (ErrCodeUtils::SUCCESS === $ret['code']) {
                //注册成功
                echo "<script>window.alert('注册成功,现在登录>>');window.location.href='login';</script>";
            } else {
                // $idPlaceholder = "该账号已存在";
                $idPlaceholder = $ret['msg'];
            }
        }
        $array['id_placeholder'] = $idPlaceholder;
        $this->assign($array); //模板赋值
        $this->display("Register/index"); //模板渲染
    }
}
