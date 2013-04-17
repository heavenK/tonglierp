<?php
class LogsAction extends CommonAction {
	public function verify(){
		
		$pass = $this->_post('action');
		
		if(!$this->_request('need_role'))	$this->error("此项目已经审核完成，无法再进行审核！");
		
		
		//	修改项目状态
		$Project = D("project");
		$p_data['pid'] = $this->_post('pid');
		
		$active = 1;
		$need_role = $this->_post('need_role');
		
		if($need_role == 7){
			$project = $Project->where($p_data)->find();
			if($project['username'] != $_SESSION['loginUserName'] && array_search(9, $_SESSION['roleId']) == FALSE)	$this->error('对不起，您没有这个权限！！');
		}elseif($need_role == 8){
			$project = $Project->where($p_data)->find();
			if($project['principal'] != $_SESSION[C('USER_AUTH_KEY')] && array_search(9, $_SESSION['roleId']) == FALSE)	$this->error('对不起，您不是该项目负责人！！');
		}else{
			if(array_search($need_role, $_SESSION['roleId']) == FALSE && array_search(9, $_SESSION['roleId']) == FALSE) $this->error('对不起，您没有这个权限！');
		}
		$roleid = $need_role;
		
		
		
		if($pass == 'pass'){
			$p_data['status'] = $need_role;
		}elseif($pass == 'nopass'){
			$p_data['status'] = -$need_role;
			$active = 0;
		}

        $list = $Project->save($p_data);
		
        if (false == $list) {
            //错误提示
            $this->error('状态修改失败!');
        }
		
		if($pass == 'submit' || $pass == 'p_submit' || $pass == 'pass') $pass = 1;
		else $pass = 0;

		$res = writeLogs($this->_post('pid'), $pass, $this->_post('content'), 'verify', $active, $roleid);

		if($res)	$this->success('审核确认完成!',cookie('_currentUrl_'));
		else	$this->error('审核确认失败!');
		
	}
}