<?php
class IndexAction extends CommonAction {
    public function index(){
		
		//	我的项目
		$Pro = D("Project");
		$my_condition['username'] = $_SESSION['loginUserName'];
		$my_condition['type'] = array('neq',7);
		$my_condition['status'] = array('neq',-1);
		
		$my_count = $Pro->where($my_condition)->count();
		$my_pros = $Pro->where($my_condition)->order("`pubdate` desc")->limit(0,10)->select();

		$this->assign('my_count', $my_count);
		$this->assign('my_pros', $my_pros);
		
		//	系统新闻
		$News = D("News");
		$news = $News->where("`type` = 'news'")->order("`pubdate` desc")->find();
		$this->assign('news', $news);
		
		//	滚动新闻
		$r_news = $News->where("`type` = 'roll'")->order("`pubdate` desc")->find();
		$this->assign('r_news', $r_news);
		
		
		//	不在线人员
		$User_status = M("User_status");
	
		$away_condition['active'] = 1;
		$now = strtotime(date("Y-m-d"));
		$away_condition['pubdate'] = array('egt',$now);
		
		$away_count = $User_status->where($away_condition)->count();
		$away_user = $User_status->where($away_condition)->order("`pubdate` desc")->select();
		$this->assign('away_count', $away_count);
		$this->assign('away_user', $away_user);
		
		
		//	我的消息
		$Mes = D("Messages");
		$m_where['roleid&kind'] =array(array("IN", $_SESSION['roleId']),"verify",'_multi'=>true);
		$m_where['kind&uid'] =array("confirm",$_SESSION['authId'],'_multi'=>true);
		$m_where['_logic'] = 'or';
		$m_condition['_complex'] = $m_where;

		$my_ver_count = $Mes->where($m_condition)->count();
		$my_ver_pros = $Mes->where($m_condition)->order("`pubdate` desc")->limit(0,10)->select();
		
		$this->assign('my_ver_count', $my_ver_count);
		$this->assign('my_ver_pros', $my_ver_pros);
		
        $this->display();
    }
	
	public function main(){
		
        $this->index();
    }
	
	public function calendar(){
		
		//	我的项目
		$Pro = D("Project");
		$my_condition['username'] = $_SESSION['loginUserName'];
		$my_condition['type'] = array('neq',7);
		$my_condition['status'] = array('neq',-1);
		

		$my_pros = $Pro->where($my_condition)->order("`pubdate` desc")->limit(0,10)->select();


		$this->assign('my_pros', $my_pros);
		
		$this->display();
	}
}