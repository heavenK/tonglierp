<?php
class FlowAction extends CommonAction {
	
	public function index() {

        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $results = $model->order('`tid` asc, `order` asc')->select();
        }
		
		foreach($results as $flow){
			
			if($flow['order'] == 1)	$flows[$flow['tid']]['flow'] = getRole($flow['roleid']);
			else	$flows[$flow['tid']]['flow'] .= "->".getRole($flow['roleid']);
		}
		
		$this->assign('list',$flows);
        $this->display();
    }
	
	public function insert(){
		$name = $this->getActionName();
        $model = D($name);
		
		
		if($this->_request('tid')) $tid = $this->_request('tid');
		else	$this->error('来源错误!');
		
		$data['tid'] = $tid;

        $result	=	$model->where($data)->find();
		if($result)	$model->where($data)->delete();
		
		foreach($this->_request('roleid') as $key => $roleid){
			if($roleid > 0){
				$data['roleid']	= $roleid;
				$data['order']	= $key + 1;
				$list = $model->relation(true)->add($data);
				if ($list == false) {
					$this->error('新增失败!');
				}
			}else{
				break;
			}
		}
		$this->success('新增成功!','__URL__');
	}
}