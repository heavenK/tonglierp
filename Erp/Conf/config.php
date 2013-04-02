<?php

$config	=	require 'config.php';

$array = array(

	// session
	/*
	'SESSION_TYPE' => 'DB',
	'SESSION_EXPIRE' => LOGIN_TIME,
	'SESSION_TABLE' => DB_PREFIX.'_session',
	*/
	// filter
	'DEFAULT_FILTER'=>'strip_tags,htmlspecialchars', 
	
	// template file suffix
	'TMPL_TEMPLATE_SUFFIX'=>'.htm',  
	
	
	// file path replace
	'TMPL_PARSE_STRING'  =>array(
     '__JS__' => '/Public/js/',		// JS file path change
     '__UPLOAD__' => '/Public/Uploads/',	//	upload file path change
	 '__CSS__' => '/Public/style/',
	),

	'TMPL_L_DELIM'=>'<{',
	'TMPL_R_DELIM'=>'}>',


    'APP_AUTOLOAD_PATH'         =>  '@.TagLib',
    'SESSION_AUTO_START'        =>  true,
	'USER_AUTH_MODEL'    =>'User',  
	'USER_AUTH_ON'   =>true,  //是否需要认证  
	'USER_AUTH_TYPE'     =>'2',   //认证类型:1为登录模式，2为实时模式  
    'USER_AUTH_KEY'		=>  'authId',	// 用户认证SESSION标记
    'ADMIN_AUTH_KEY'	=>  'administrator',
	'REQUIRE_AUTH_MODULE'   =>'',     //需要认证模块（模块名之间用短号分开）  
	'NOT_AUTH_MODULE'    =>'Public',    //无需认证模块（模块名之间用短号分开）  
	'REQUIRE_AUTH_ACTION'   =>'',     //需要认证方法（方法名之间用短号分开）  
	'NOT_AUTH_ACTION'    =>'',    //无需认证方法（方法名之间用短号分开）  
	'USER_AUTH_GATEWAY'  =>'/Public/login',    //认证网关  
        'RBAC_ROLE_TABLE'           =>'tl_role',
        'RBAC_USER_TABLE'           =>'tl_role_user',
        'RBAC_ACCESS_TABLE'         =>'tl_access',
        'RBAC_NODE_TABLE'           =>'tl_node',
	
);

return array_merge($config,$array);
?>