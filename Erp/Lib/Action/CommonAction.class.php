<?php

class CommonAction extends Action {

    function _initialize() {
        import('@.ORG.Util.Cookie');
        // 用户权限检查
        if (C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))) {
            import('@.ORG.Util.RBAC');
            if (!RBAC::AccessDecision()) {
                //检查认证识别号
                if (!$_SESSION [C('USER_AUTH_KEY')]) {
                    //跳转到认证网关
                    redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
                }
                // 没有权限 抛出错误
                if (C('RBAC_ERROR_PAGE')) {
                    // 定义权限错误页面
                    redirect(C('RBAC_ERROR_PAGE'));
                } else {
                    if (C('GUEST_AUTH_ON')) {
                        $this->assign('jumpUrl', PHP_FILE . C('USER_AUTH_GATEWAY'));
                    }
                    // 提示错误信息
                    $this->error(L('_VALID_ACCESS_'));
                }
            }
        }
    }

    public function index() {
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
        $this->display();
        return;
    }

    /**
      +----------------------------------------------------------
     * 取得操作成功后要返回的URL地址
     * 默认返回当前模块的默认操作
     * 可以在action控制器中重载
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    function getReturnUrl() {
        return __URL__ . '?' . C('VAR_MODULE') . '=' . MODULE_NAME . '&' . C('VAR_ACTION') . '=' . C('DEFAULT_ACTION');
    }

    /**
      +----------------------------------------------------------
     * 根据表单生成查询条件
     * 进行列表过滤
      +----------------------------------------------------------
     * @access protected
      +----------------------------------------------------------
     * @param string $name 数据对象名称
      +----------------------------------------------------------
     * @return HashMap
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    protected function _search($name = '') {
        //生成查询条件
        if (empty($name)) {
            $name = $this->getActionName();
        }
        $name = $this->getActionName();
        $model = D($name);
        $map = array();
        foreach ($model->getDbFields() as $key => $val) {
            if (isset($_REQUEST [$val]) && $_REQUEST [$val] != '') {
				$map [$val] = $_REQUEST [$val];
            }
        }
		if(isset($_REQUEST ['keyword']))	$map ['sn|pname'] = array('LIKE','%'.$_REQUEST ['keyword']."%");
        return $map;
    }

    /**
      +----------------------------------------------------------
     * 根据表单生成查询条件
     * 进行列表过滤
      +----------------------------------------------------------
     * @access protected
      +----------------------------------------------------------
     * @param Model $model 数据对象
     * @param HashMap $map 过滤条件
     * @param string $sortBy 排序
     * @param boolean $asc 是否正序
      +----------------------------------------------------------
     * @return void
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    protected function _list($model, $map, $sortBy = '', $asc = false) {
        //排序字段 默认为主键名
        if (isset($_REQUEST ['_order'])) {
            $order = $_REQUEST ['_order'];
        } else {
            $order = !empty($sortBy) ? $sortBy : $model->getPk();
        }
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = $asc ? 'asc' : 'desc';
        }
        //取得满足条件的记录数
        $count = $model->where($map)->count($model->getPk());

        if ($count > 0) {
            import("@.ORG.Util.Page");
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = '';
            }
            $p = new Page($count, $listRows);
            //分页查询数据

            $voList = $model->relation(true)->where($map)->order("`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            //echo $model->getlastsql();
            //分页跳转的时候保证查询条件
            foreach ($map as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示
			$p->setConfig('theme',"<li><a>%totalRow% %header% %nowPage%/%totalPage% 页</a></li>%upPage% %first% %prePage% %linkPage% %nextPage% %end% %downPage%");
            $page = $p->show();
            //列表排序显示
            $sortImg = $sort; //排序图标
            $sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
            $sort = $sort == 'desc' ? 1 : 0; //排序方式
            //模板赋值显示
            $this->assign('list', $voList);
            $this->assign('sort', $sort);
            $this->assign('order', $order);
            $this->assign('sortImg', $sortImg);
            $this->assign('sortType', $sortAlt);
            $this->assign("page", $page);
        }
		if($model != 'Logs')	cookie('_currentUrl_', __SELF__);
        return;
    }

    function insert() {
		
		
        $name = $this->getActionName();
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        //保存当前数据对象
        $list = $model->relation(true)->add();
		
        if ($list !== false) { //保存成功
		
			//	处理附件
			$r = uploadFiles($list);
			if($r){
				$att_date['pid'] = $list;
				$att_date['attachment'] = $r;
				$model->save($att_date);
			}
			
			//记录用户操作
			writeLogs($list, '', "成功添加新任务！");
			
            $this->success('新增成功!',cookie('_currentUrl_'));
        } else {
            //失败提示
            $this->error('新增失败!');
        }
		
    }
	

	//	一对多关系时的插入
    function many_insert() {
        $name = $this->getActionName();
        $model = D($name);
		$datas = $model->create();
        if (false === $datas) {
            $this->error($model->getError());
        }
		
		//	单独处理造价子项
		$subitems = $datas['cost_subitem'];
		unset($datas['cost_subitem']);

        //保存当前数据对象
        $list = $model->relation(true)->add($datas);
        if ($list !== false) { //保存成功
			
			//	处理附件
			$r = uploadFiles($list);
			if($r){
				$att_date['pid'] = $list;
				$att_date['attachment'] = $r;
				$model->save($att_date);
			}
		
			//记录用户操作
			writeLogs($list, '', "成功添加新任务！");
		
			//	重新构造子项数据。
			foreach($subitems as $key => $arr){
				$item = array();
				$item['sn'] = $datas['sn'] . "-" . $key;
				$item['pname'] = $arr['pname'];
				$item['type'] = 7;
				$item['cost_subitem']['cid'] = $list;
				$item['cost_subitem']['submitMoney'] = $arr['submitMoney'];
				$item['cost_subitem']['judgeMoney'] = $arr['judgeMoney'];
				$item['cost_subitem']['reduceMoney'] = $arr['reduceMoney'];

				$item = $model->create($item);
				$res = $model->relation(true)->add($item);
				writeLogs($res, '', "成功添加造价子项任务！");
			}
            $this->success('新增成功!',cookie('_currentUrl_'));
        } else {
            //失败提示
            $this->error('新增失败!');
        }
    }

    public function add() {
        $this->display();
    }

    function read() {
        $this->edit();
    }

    function edit() {
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
        $this->display();
    }

    function update() {
        $name = $this->getActionName();
        $model = D($name);
		
		if($name=="Project"){
			$pro = $model->where("`pid` = ".$this->_request('pid'))->find();
			
			if($pro['status'] > 1) $this->error('此项目正在审核流程中，无法进行修改!');
			if($pro['username'] != $_SESSION['loginUserName'] && array_search(9, $_SESSION['roleId']) == FALSE)	$this->error('您没有修改这个项目的权限！');
		}
		
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        // 更新数据
        $list = $model->relation(true)->save();
        if (false !== $list) {
			
			//	处理附件
			$r = uploadFiles($this->_request('pid'));
			if($r){
				$att_date['pid'] = $this->_request('pid');
				$att_date['attachment'] = $r;
				$res = $model->save($att_date);
			}
			//记录用户操作
			writeLogs($this->_request('pid'), '', "成功编辑任务！");
			
            //成功提示
            $this->success('编辑成功!',cookie('_currentUrl_'));
        } else {
            //错误提示
            $this->error('编辑失败!');
        }
    }
	
	//	一对多关系时的更新
	function many_update() {
        $name = $this->getActionName();
        $model = D($name);
		
		if($name=="Project"){
			$pro = $model->where("`pid` = ".$this->_request('pid'))->find();
			
			if($pro['status'] > 1) $this->error('此项目正在审核流程中，无法进行修改!');
			if($pro['username'] != $_SESSION['loginUserName'] && array_search(9, $_SESSION['roleId']) == FALSE)	$this->error('您没有修改这个项目的权限！');
		}
		
		$datas = $model->create();
        if (false === $datas) {
            $this->error($model->getError());
        }
		
		//	单独处理造价子项
		$subitems = $datas['cost_subitem'];
		//unset($datas['cost_subitem']);

        //保存当前数据对象
		
        // 更新数据
        $list = $model->relation(true)->save($datas);
        if (false !== $list) {
			
			//	处理附件
			$r = uploadFiles($this->_request('pid'));
			if($r){
				$att_date['pid'] = $this->_request('pid');
				$att_date['attachment'] = $r;
				$res = $model->save($att_date);
			}
			
			//记录用户操作
			writeLogs($this->_request('pid'), '', "成功编辑任务！");
			
			//	重新构造子项数据。
			foreach($subitems as $key => $arr){
				$item = array();
				if($arr['pid'])	{
					$item['pid'] = $arr['pid']; 
					
					
				}else{
					
					$item['type'] = 7;
					$item['cost_subitem']['cid'] = $this->_request('pid');
				}
				$item['sn'] = $datas['sn'] . "-" . $key;
				$item['pname'] = $arr['pname'];
				$item['cost_subitem']['submitMoney'] = $arr['submitMoney'];
				$item['cost_subitem']['judgeMoney'] = $arr['judgeMoney'];
				$item['cost_subitem']['reduceMoney'] = $arr['reduceMoney'];

				$item = $model->create($item);
				if($arr['pid'])	{
					$res = $model->relation(true)->save($item);
					
					//记录用户操作
					writeLogs($arr['pid'], '', "成功编辑造价子项！");
					
				}else{
					$res = $model->relation(true)->add($item);
					//记录用户操作
					writeLogs($res, '', "成功添加造价子项！");
				}
				
			}
			
            //成功提示
            $this->success('编辑成功!',cookie('_currentUrl_'));
        } else {
            //错误提示
            $this->error('编辑失败!');
        }
    }

    /**
      +----------------------------------------------------------
     * 默认删除操作
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    public function delete() {
        //删除指定记录
        $name = $this->getActionName();
        $model = M($name);
		
		if($name=="Project" && array_search(9, $_SESSION['roleId']) == FALSE && array_search(13, $_SESSION['roleId']) == FALSE  && array_search(14, $_SESSION['roleId']) == FALSE){
			$pro = $model->where("`pid` = ".$this->_request('pid'))->find();
			
			if($pro['status'] > 1) $this->error('此项目正在审核流程中，无法进行删除!');
			if($pro['username'] != $_SESSION['loginUserName'])	$this->error('您没有删除这个项目的权限！');
		}
		
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST [$pk];
            if (isset($id)) {
                $condition = array($pk => array('in', explode(',', $id)));
                $list = $model->where($condition)->setField('status', - 1);
                if ($list !== false) {
					//记录用户操作
					writeLogs($id, '', "成功删除任务！");
					
                    $this->success('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error('非法操作');
            }
        }
    }
	
	
	public function foreverdelete() {
        //删除指定记录
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST [$pk];
            if (isset($id)) {

				$condition = array($pk => array('in', explode(',', $id)));
				if (false !== $model->relation(true)->where($condition)->delete()) {
					
					if($name=="Project"){
						$condition_info = array($pk => array('in', explode(',', $id)));
						
						$Messages = D("Messages");
						$Messages->where($condition_info)->delete();
						
						$Logs = D("Logs");
						$Logs->where($condition_info)->delete();
					}
					
					
					$this->success('删除成功！');
				} else {
					$this->error('删除失败！');
				}

            } else {
                $this->error('非法操作');
            }
        }
    }
	
	/**
      +----------------------------------------------------------
     * 默认恢复操作
     *
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws FcsException
      +----------------------------------------------------------
     */
    public function resume() {
        //恢复指定记录
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $_GET [$pk];
        $condition = array($pk => array('in', $id));
        if (false !== $model->resume($condition)) {
			//记录用户操作
			writeLogs($id, '', "成功恢复任务！");
			
            $this->success('状态恢复成功！',cookie('_currentUrl_'));
        } else {
            $this->error('状态恢复失败！');
        }
    }
}