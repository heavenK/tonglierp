<?php
class PublicAction extends Action {
    // 检查用户是否登录
    protected function checkUser() {
        if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
            $this->error('没有登录','Public/login');
        }
    }


    // 用户登录页面
    public function login() {
        if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
            $this->display();
        }else{
            $this->redirect('Index/index');
        }
    }

    public function index() {
        //如果通过认证跳转到首页
        redirect(__APP__);
    }

    // 用户登出
    public function logout() {
        if(isset($_SESSION[C('USER_AUTH_KEY')])) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION);
            session_destroy();
            $this->success('登出成功！',__URL__.'/login/');
        }else {
            $this->error('已经登出！');
        }
    }

    // 登录检测
    public function checkLogin() {
        if(empty($_POST['account'])) {
            $this->error('帐号错误！');
        }elseif (empty($_POST['password'])){
            $this->error('密码必须！');
        }elseif (empty($_POST['verify'])){
            $this->error('验证码必须！');
        }
        //生成认证条件
        $map            =   array();
        // 支持使用绑定帐号登录
        $map['account']	= $_POST['account'];
        $map["status"]	=	array('gt',0);
        if(session('verify') != md5($_POST['verify'])) {
            $this->error('验证码错误！');
        }
        import ( '@.ORG.Util.RBAC' );
        $authInfo = RBAC::authenticate($map);
        //使用用户名、密码和状态的方式进行认证
        if(false === $authInfo) {
            $this->error('帐号不存在或已禁用！');
        }else {
            if($authInfo['password'] != md5($_POST['password'])) {
                $this->error('密码错误！');
            }
			
			//	获取用户组角色ID
			$Role = M("role_user");
			$role_ids = $Role->where("`user_id` = ".$authInfo['id'])->select();
			foreach($role_ids as $key => $val){
				$role_id[] = $val['role_id'];
			}
			
			
            $_SESSION[C('USER_AUTH_KEY')]	=	$authInfo['id'];
			$_SESSION['roleId']	=	$role_id;
            $_SESSION['email']	=	$authInfo['email'];
            $_SESSION['loginUserName']		=	$authInfo['nickname'];
            $_SESSION['lastLoginTime']		=	$authInfo['last_login_time'];
            $_SESSION['login_count']	=	$authInfo['login_count'];
            if($authInfo['account']=='admin') {
                $_SESSION['administrator']		=	true;
            }
            //保存登录信息
            $User	=	M('User');
            $ip		=	get_client_ip();
            $time	=	time();
            $data = array();
            $data['id']	=	$authInfo['id'];
            $data['last_login_time']	=	$time;
            $data['login_count']	=	array('exp','login_count+1');
            $data['last_login_ip']	=	$ip;
            $User->save($data);

            // 缓存访问权限
            RBAC::saveAccessList();
            $this->success('登录成功！',__APP__.'/Index/index');

        }
    }
    // 更换密码
    public function changePwd() {
        $this->checkUser();
        //对表单提交处理进行处理或者增加非表单数据
        if(md5($_POST['verify'])	!= $_SESSION['verify']) {
            $this->error('验证码错误！');
        }
        $map	=	array();
        $map['password']= pwdHash($_POST['oldpassword']);
        if(isset($_POST['account'])) {
            $map['account']	 =	 $_POST['account'];
        }elseif(isset($_SESSION[C('USER_AUTH_KEY')])) {
            $map['id']		=	$_SESSION[C('USER_AUTH_KEY')];
        }
        //检查用户
        $User    =   M("User");
        if(!$User->where($map)->field('id')->find()) {
            $this->error('旧密码不符或者用户名错误！');
        }else {
            $User->password	=	pwdHash($_POST['password']);
            $User->save();
            $this->success('密码修改成功！');
         }
    }

    public function profile() {
        $this->checkUser();
        $User	 =	 M("User");
        $vo	=	$User->getById($_SESSION[C('USER_AUTH_KEY')]);
        $this->assign('vo',$vo);
        $this->display();
    }

    public function verify() {
        $type	 =	 isset($_GET['type'])?$_GET['type']:'gif';
        import("@.ORG.Util.Image");
        Image::buildImageVerify(4,1,$type);
    }
	
	
	public function wordTOpdf() {
		
		set_time_limit(0); 

		
        $pid	 =	 isset($_GET['pid'])?$this->_GET('pid'):$this->error('无法转换!');
		$Pro	 =	 M("Project");
        $vo	=	$Pro->getByPid($pid);
		
		$doc_file = WEB_ROOT."/Public/Uploads/".$vo['attachment']; 
		
		$pdf_path = explode('/',$vo['attachment']);
		
		$output_file = WEB_ROOT."/Public/Uploads/".$pdf_path['0']."/".$pdf_path['1']."/"; 
		
		exec("unoconv -f pdf -o ".$output_file." ".$doc_file);
		
		// windows 用的转换过程
		/*
		$doc_file = "file:///".WEB_ROOT."/Public/Uploads/".$vo['attachment']; 
		$output_file = "file:///".WEB_ROOT."/Public/Uploads/".$vo['attachment'].".pdf"; 
		if(!word2pdf($doc_file,$output_file)) $this->error("您的附件不是doc形式，无法转换！");
		
		*/
		//$this->success('转换成功', "/Public/Uploads/".$vo['attachment'].".pdf");
		
		//php 强制下载PDF文件
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename='.$vo['sn']);
		
		$pdf_file = str_replace(".doc",".pdf",$vo['attachment']);
		
		readfile(WEB_ROOT."/Public/Uploads/".$pdf_file);
    }

    // 修改资料
    public function change() {
        $this->checkUser();
        $User	 =	 D("User");
        if(!$User->create()) {
            $this->error($User->getError());
        }
        $result	=	$User->save();
        if(false !== $result) {
            $this->success('资料修改成功！');
        }else{
            $this->error('资料修改失败!');
        }
    }
	
	//	人员状态
	public function goOut() {
		$this->checkUser();
        $User	 =	 D("User_status");
		
		$condition['username'] = $_SESSION['loginUserName'];
		$res = $User->where($condition)->setField('active',0);
		
		if($this->_request('type') == 2){
			if ($res !== false) { //保存成功
	
				$this->success('修改成功！',cookie('_currentUrl_'));
			} else {
				//失败提示
				$this->error('修改失败!');
			}
			exit;
		}
		
		$data['reason'] = $this->_request('reason');
		
		$data = $User->create($data);
		
        if (false === $data) {
            $this->error($User->getError());
        }
		
		//保存当前数据对象
        $list = $User->add($data);
		
		if ($list !== false) { //保存成功

            $this->success('修改成功！',cookie('_currentUrl_'));
        } else {
            //失败提示
            $this->error('修改失败!');
        }
    }
}