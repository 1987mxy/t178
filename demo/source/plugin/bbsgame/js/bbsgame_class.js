// JavaScript Document
//@require("jquery1.7.min.js");
/**
*
* Author:AgudaZaric
* e-mail:coderzl@hotmail.com
* QQ:384318815
*
**/

/**
* class game
*
* -id 游戏id.
*/
var jq = jQuery.noConflict();

function game(id,ifInstall) {
	this.id = id;
	this.ifInstall = ifInstall;
	this.selector;
}
/**
* methods
**/
game.prototype = {
 install:function() {
	if(this.ifInstall == 1) {
		alert("game has been installed");
		return false;
	}
	var selector = this.selector;
	jq.ajax({
		 url:window.location.pathname+"?adminjob=hack&hackset=bbsgame&do=ajax&op=install&id="+this.id+"&t="+Math.random(),
		 dataType:'json',
		 success:function(data){
			 try{
				var arr = eval(data);
				if(arr.ret){
					alert("安装完成");
					selector.parent().html("安装完成");
				}else{
					alert("安装失败，原因:"+arr.msg);
				}
				jq("#pw_box").css('display','none');
			 }catch(e){
				//alert(e);	
			 }	
		 },
		beforeSend:loading()
	});
 },
 uninstall:function() {
	if(confirm('确定要删除？') == false) return;
	var selector = this.selector;
	var xht = jq.ajax({
		 url:window.location.pathname+"?adminjob=hack&hackset=bbsgame&do=ajax&op=uninstall&id="+this.id+"&t="+Math.random(),
		 dataType:'json',
		 success:function(data){
			 try{
				var arr = eval(data);
				if(arr.ret){
					alert("卸载完成");
					selector.parent().html("卸载完成");
					jq("#pw_box").css('display','none');
				} else {
					alert("卸载失败，原因:"+arr.msg);	
				}
			 }catch(e){
				//alert(e);	
			 }
	   }	 
 });
},
 bind:function(selector,method) {
		jq(selector).data("game",this);
		jq(selector).click(function() {		
			var game = jq(this).data("game");
			game.selector = jq(this);
			eval("game."+method+"();");
        });	
	}
};

/**
* mode visible management
* -selected 已选择项
* -name modename
* -mid 模块id
* -idetifier 用于绑定事件的对象标识,DOM
* -maxSelectedCount 模块列表最大显示条数
*/

function mode(selected, name, iden, maxSelectedCount) {
	if(typeof(maxSelectedCount) == 'undefined') maxSelectedCount = 1000;
	//this.__PRO__ = {1,2,3};
	if( typeof(selected) == 'undefined') selected = {};
	this.selectedGames = selected.games;
	this.selectedTypes = selected.types;
	if( typeof(this.selectedGames) == 'undefined' ) this.selectedGames = [];
	if( typeof(this.selectedTypes) == 'undefined' ) this.selectedTypes = [];
	this.shown = 1;
	this.iden = iden;
	this.maxSelectedCount = maxSelectedCount;
	this.name = name;

}

mode.prototype = {
	update:function(modeclassname) {
	   if(this.selectedGames != '' || this.selectedTypes != "" || this.name != '') {		
		jq.ajax({
			url:window.location.pathname+"?action=plugins&operation=config&do=7&identifier=bbsgame&pmod=adminmain&act=tpl&name="+this.name+"&gids="+encodeURI(this.selectedGames)+"&tids="+encodeURI(this.selectedTypes)+"&classname="+modeclassname+"&iden="+this.iden+"&inajax=1",
			dataType:'json',
			success:function(data){
				try{
					var result = eval(data);
					if(result.ret==0)
						alert(result.msg);
					else
						alert("更新完成");
				} catch(e) {
						alert(e);
				}
			},
			error:function(data){
				alert("更新完成");
			}
		});	
	  }//end if
	},//end update
	_delete:function(modeclassname){
		jq.ajax({
			url:window.location.pathname+"?action=plugins&operation=config&do=7&identifier=bbsgame&pmod=adminmain&act=tpl&step=delete&&classname="+modeclassname+"&iden="+this.iden+"&inajax=1",
			dataType:'json',
			success:function(data){
				try{
					var result = eval(data);
					if(result.ret == 0) {
						alert(result.msg);
					}
				} catch(e) {
					//	alert(e);	
				}
			}	   
		});	
		return false;
	},
	config : function(games){
		if(typeof(games) != 'undefined' && games != '') {
			var items = (games + ",").split(",");
			this.selectedTypes = new Array();
			this.selectedGames = new Array();
	        for (var i = 0; i < items.length - 1; i++) {
        	    var type = items[i].substr(0, 1);
              	var id = items[i].substr(1);
               	if (type == "b") {
                    for (var j = 0; j < arrclass.length; j++) {
                        if (parseInt(id) == arrclass[j].classId ) {
                            this.selectedTypes.push(arrclass[j].classId);
                        }
                    }
                } else if (type == "s") {
                    for (var j = 0; j < arrjob.length; j++) {
                        if ( parseInt(id) == arrjob[j].itemId ) {
                            this.selectedGames.push(arrjob[j].itemId);
                        }
                    }
                }
            }
		}
		//this.update();
	},
	bind : function(callback,mode) {
					var value = '';
					for(var i in this.selectedGames) {
						value += "s"+this.selectedGames[i]+",";	
					}
					for(var i in this.selectedTypes) {
						value += "b"+this.selectedTypes[i]+",";	
					}
					Boxy.job(value, this.shown, this.maxSelectedCount, function(val) {eval("mode."+callback+"('"+val+"');")}, {"title":"展示列表","draggable":true});
					return false;
	}
}

/*
* functions 
*/

function loading(){
	var imgSrc = "source/plugin/bbsgame/images/waitloadding.gif";
	jq("#pw_box").css({
				'left':0,
				'background':'url('+imgSrc+') center center no-repeat gray',
				'width':'101%',
				'height':'110%',
				'opacity':0.8,
				'display':'block'
				 });
	var image = new Image();
	image.src = imgSrc;
	var left = (parseInt(jq("#pw_box").css("width")) - image.width) / 2;
	jq("#pw_box").html("<div style='position:absolute; width:150px; height:30px; top:50px; left:"+left+"px; font-size:18px; color:#F3D09B;'>正在安装请稍等</div>");
}

function modeupdate(event){
	var parent =  jq(this).parents("tr[id*=subm]:first");
	var name = parent.children().has("input[name=name]").children("input[name=name]").val();
	var modeexist = false;
	if(typeof(name) == 'undefined' || name == '') {
		alert("请输入模块名称");
		return false;
	}
	var iden = parent.attr("id");
	var modetype = iden.substr(0,1);
	if(modetype == 'J') {
		temp = JLJF;
		modeclass = "JLJF";
	} 
	else if ( modetype == 'P') {
		temp = JFPK;
		modeclass = "JFPK";
	}
	if( typeof(temp) != 'undefined' ) {
		for(var i in temp) {
			if( temp[i].iden == iden ) {
				modeexist = true;
				temp[i].name = name;
				temp[i].update(modeclass);
			}
		}
	}
	if(!modeexist) {
		temp.push(new mode({},name, iden,100));
		temp = null;
	}
}

	
function modeconfig(event){
	var iden = jq(this).parents("tr[id*=subm]:first").attr("id");
	var modetype = iden.substr(0,1);
	var exist = false;
	var m;
	if(modetype == 'J') {
		temp = JLJF;
	} 
	else if ( modetype == 'P') {
		temp = JFPK;
	}
	if( typeof(temp) != 'undefined' ) {
		for(var i in temp) {
			if( temp[i].iden == iden) {
				exist = true;
				m = temp[i];
			}
		}
		if( exist == false ) {
			var m = new mode({},'',iden,100);
			temp.push(m);
			temp = null;
		}
		m.bind("config",m);
	}
}


function modeadd(){
	var id = jq(this).parent("td").attr("id");
	switch(id){
		case "JLJF" :
			if(id.length > 1) {
				alert("抱歉，奖励积分只允许添加一条");
				return false;
			}
			appendItem = jq("[id*=J_subm]:first");
			iden = generateIdRecursive("J",eval(id+".length+1"));
			regexp = /J_subm(\d+)/g;
			break;
		case "JFPK" :
			appendItem = jq("[id*=P_subm]:first");
			iden = generateIdRecursive("P",eval(id+".length+1"));
			regexp = /P_subm(\d+)/g;
			break;
		default:
			break;
	}
	var newItem = appendItem.clone();
	newItem.css("display","");
	if(typeof(newItem) == 'undefined' || typeof(iden) == 'undefined') return false;
	str = "<tr>"+newItem.outerHTML().replace(regexp,iden)+"</tr>";
	appendItem.parent().append(str);
	eval(id+".push(new mode({}, '', iden ))");
	jq(".modeconfig").unbind("click");
	jq(".modeupdate").unbind("click");
	jq(".modeupdate").click(modeupdate);
	jq(".modeconfig").click(modeconfig);
	jq(".modedelete").click(modedelete);
}


function generateIdRecursive(modePre, index){
	iden = modePre+"_subm"+index;
	if(jq("[id="+iden+"]").length != 0) {
		return generateIdRecursive(modePre, index+1);
	}
	else {
		return iden;
	}
}


function modedelete(){
	if( !confirm("确定要此模块删除吗？")) return false;
	var removeItem = jq(this).parents("[id*=_subm]:first");
	var iden =removeItem.attr("id");
	var modetype = iden.substr(0,1);
	if( modetype == 'J' ) {
		temp = JLJF;
		modeclass = "JLJF";
	} 
	else if ( modetype == 'P') {
		temp = JFPK;
		modeclass = "JFPK";
	}
	
	for( var i in temp ) {
		if( temp[i].iden == iden ) {
			//doAjax
			temp[i]._delete(modeclass);
			temp.splice(i,1);
		}	
	}
	removeItem.remove();
}