<?php
class NewsAction extends CommonAction {

	public function lists() {
		
		$type = $this->_get('type');
		
		
		//	跨域提交链接
		if($type == 'zhaobiao'){
			$iframe_url = WWW_PATH."/tl_admin/content_list.php?channelid=1&cid=6";	
		}elseif($type == 'zhongbiao'){
			$iframe_url = WWW_PATH."/tl_admin/content_list.php?channelid=1&cid=7";	
		}

		$this->assign("iframe_url",$iframe_url);
		$this->display('iframe');
	}

	public function insert() {
		
        $name = $this->getActionName();
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        //保存当前数据对象
        $list = $model->add();
		
        if ($list !== false) { //保存成功
		
			//	处理附件
			$r = uploadFiles($list);
			if($r){
				$att_date['id'] = $list;
				$att_date['litpic'] = $r;
				$model->save($att_date);
			}

            $this->success('新增成功!',cookie('_currentUrl_'));
        } else {
            //失败提示
            $this->error('新增失败!');
        }
		
    }
	
	function update() {
        $name = $this->getActionName();
        $model = D($name);

        if (false === $model->create()) {
            $this->error($model->getError());
        }
        // 更新数据
        $list = $model->save();
		

        if (false !== $list) {
			
			//	处理附件
			$r = uploadFiles($this->_request('id'));
			if($r){
				$att_date['id'] = $this->_request('id');
				$att_date['litpic'] = $r;
				$res = $model->save($att_date);
			}
			
            //成功提示
            $this->success('编辑成功!',cookie('_currentUrl_'));
        } else {
            //错误提示
            $this->error('编辑失败!');
        }
    }
}