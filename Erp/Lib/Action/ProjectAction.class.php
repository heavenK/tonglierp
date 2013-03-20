<?php
class ProjectAction extends CommonAction {
	
	
	//	列表过滤器
	function _filter(&$map){
        if(!empty($_POST['name'])) {
        $map['title'] = array('like',"%".$_POST['name']."%");
        }
		if(!isset($_REQUEST['status'])) $map['status'] = array('neq',-1);
		
		if(!isset($_REQUEST['type'])) $map['type'] = array('neq',7);
    }
	
	//	招标通知
	public function tenders_notice(){
		$_REQUEST['type'] = 1;
		
		$this->index();
	}
	
	
	//	添加招标通知
	public function tenders_notice_add(){
		$this->add();
	}
	
	//	修改招标通知
	public function tenders_notice_edit(){
		$this->edit();
	}
	
	
	//	项目回收站
	public function rubish(){
		$_REQUEST['status'] = -1;
		
		$this->index();
	}
	
	//	招标项目
	public function tenders(){
		$this->assign('type', $this->_get('type'));
		$this->index();
	}
	
	//	添加招标项目
	public function tenders_add(){
		$this->assign('type', $this->_get('type'));
		$this->add();
	}
	
	//	编辑招标项目
	public function tenders_edit(){
		$this->assign('type', $this->_get('type'));
		$this->edit();
	}
	
	//	造价通知
	public function cost_notice(){
		$_REQUEST['type'] = 4;
		$this->index();
	}
	
	//	添加招标通知
	public function cost_notice_add(){
		$this->add();
	}
	
	//	修改造价通知
	public function cost_notice_edit(){
		$this->edit();
	}
	
	//	造价项目
	public function cost(){
		$this->assign('type', $this->_get('type'));
		$this->index();
	}
	
	//	添加招标项目
	public function cost_add(){
		$this->assign('type', $this->_get('type'));
		$this->add();
	}
	
	//	编辑造价项目
	public function cost_edit(){
		$this->assign('type', $this->_get('type'));
		$this->edit();
	}
	
	//	审核功能
	public function verify(){
		
		$action = $this->_get('action');
		
		if(!$action){		//	列表
			//列表过滤器，生成查询Map对象
			$map = $this->_search();
			if (method_exists($this, '_filter')) {
				$this->_filter($map);
			}
			$name = $this->getActionName();
			$model = D($name);
			if (!empty($model)) {
				$this->_list($model, $map);
			}
			$template = '';
			if($this->_request('type') == 1)	$template = "verify_tenders_notice";
			if($this->_request('type') == 4)	$template = "verify_cost_notice";
			if($this->_request('type') == 2 || $this->_request('type') == 3)	$template = "verify_tenders";
			if($this->_request('type') == 5 || $this->_request('type') == 6)	$template = "verify_cost";
		}elseif($action == 'show'){
			$name = $this->getActionName();
			$model = D($name);
			$id = $_REQUEST [$model->getPk()];
			
			$pk = "getBy".$model->getPk();
	
			$vo = $model->relation(true)->$pk($id);
			
			if($vo['type'] == 5 || $vo['type'] == 6){
				$condition['sn'] = array('LIKE',$vo['sn'].'_%');
				$sum = $model->where($condition)->count();
				$vo_sub = $model->where($condition)->relation(true)->select();
				$this->assign('sum', $sum);
				$this->assign('vo_sub', $vo_sub);
			}
			
			$this->assign('vo', $vo);
			
			
			//	获取已成立流程
			$verify = getVerify($id,$vo['type']);
			foreach($verify as $key => $val){
				if(!$val['username']){
					$need_role = $val['roleid'];
					$order = $key+1;
					break;
				}
			}
			
			//	获取日志
			$Logs = D("logs");
			$l_condition['pid'] = $id;
			$l_condition['kind'] = "verify";
			
			$count = $Logs->where($l_condition)->count('id');
			if ($count > 0) {
				import("@.ORG.Util.Page");
				//创建分页对象
				if (!empty($_REQUEST ['listRows'])) {
					$listRows = $_REQUEST ['listRows'];
				} else {
					$listRows = 5;
				}
				$p = new Page($count, $listRows);
				//分页查询数据
	
				$voList = $Logs->where($l_condition)->order("pubdate DESC")->limit($p->firstRow . ',' . $p->listRows)->select();
				//echo $model->getlastsql();
				//分页跳转的时候保证查询条件
				foreach ($l_condition as $key => $val) {
					if (!is_array($val)) {
						$p->parameter .= "$key=" . urlencode($val) . "&";
					}
				}
				//分页显示
				$p->setConfig('theme',"<li><a>%totalRow% %header% %nowPage%/%totalPage% 页</a></li>%upPage% %first% %prePage% %linkPage% %nextPage% %end% %downPage%");
				$page = $p->show();

				//模板赋值显示
				$this->assign('list', $voList);
				$this->assign("page", $page);
			}
			
			$this->assign('verify', $verify);
			$this->assign('need_role', $need_role);
			$this->assign('order', $order);
			
			if($this->_request('type') == 1)	$template = "verify_tenders_notice_show";
			if($this->_request('type') == 4)	$template = "verify_cost_notice_show";
			if($this->_request('type') == 2 || $this->_request('type') == 3)	$template = "verify_tenders_show";
			if($this->_request('type') == 5 || $this->_request('type') == 6)	$template = "verify_cost_show";
		}
		
		$this->assign('type', $this->_get('type'));
        $this->display($template);
	}
}