//	添加造价子项
function add_subitem(type){
	
	var id = $("#sub_id").val();
	var newid = parseInt(id) + 1;
	$("#sub_id").val(newid);
	
	var str = '<tr>';
    str += '<td>' + newid + '</td>';
    str += '<td><input type="text" name="cost_subitem[' + newid + '][pname]" value="" class="input-xlarge"></td>';
	
	if(type == 1){
		str += '<td><input type="text" name="cost_subitem[' + newid + '][submitMoney]" value="" class="input-xlarge"></td>';
		str += '<td><input type="text" name="cost_subitem[' + newid + '][judgeMoney]" value="" class="input-xlarge"></td>';
		str += '<td><input type="text" name="cost_subitem[' + newid + '][reduceMoney]" value="" class="input-xlarge"></td>';
	}
    str += '</tr>';
	
	$(".cost_subitem_s").append(str);
}


//	弹出操作记录框
function WinOp(url)
{
	window.open(url, '', 'width=600 height=350 scrollbars=no toolbar=no top=60 left=60 resizable=yes')
}

//	流程定制角色选择
function add_flowRole(id){

	var next_id = id + 1;
	if(!$("#role_" + next_id).html()){
		var str = ' -> ';
		str += '<select name="roleid[]" onchange="add_flowRole(' + next_id + ');" id="role_' + next_id + '" class="input-xlarge">';
        str +=	'<option value="0">结束</option>';
        str +=	$("#roles").html();
   		str +=	'</select>';
		$("#role_" + id).after(str);
	}
}

//	审核提交
function verify(action){
	
	var content = $("#content").val();
	
	var str = '';
	if(action == 'submit' || action == 'p_submit')	str = '你确认要进行审核提交吗？';
	if(action == 'pass')	str = '你确定审核通过吗？';
	if(action == 'nopass')	str = '你确定审核不通过吗？';
	
	
	$("#action").attr('value',action);	
	
	if(!content){
		if(confirm("您没有填写意见，确认这样提交吗？")){
			form1.submit();
		}else{
			return false;	
		}
	}
	
	if(confirm(str)){
		form1.submit();
	}else{
		return false;
	}
}