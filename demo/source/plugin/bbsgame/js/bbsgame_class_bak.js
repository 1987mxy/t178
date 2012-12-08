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
		 url:window.location.pathname+"?action=plugins&operation=config&do={$_GET['do']}&identifier=bbsgame&pmod=adminmain&op=install&id="+this.id+"&t="+Math.random()+this.iden+"&inajax=1",
		 dataType:'json',
		 success:function(data){
			 try{
				var arr = eval(data);
				if(arr.ret){
					alert("安装完成");
					selector.parent().html("安装完成");
					jq("#pw_box").css('display','none');
				}else{
					alert("安装失败，原因:"+arr.msg);
				}
			 }catch(e){
				alert(e);	
			 }	
		 },
		beforeSend:loading()
	});
 },
 uninstall:function() {
	if(confirm('确定要删除？') == false) return;
	var selector = this.selector;
	var xht = jq.ajax({
		 url:window.location.pathname+"?action=plugins&operation=config&do={$_GET['do']}&identifier=bbsgame&pmod=adminmain&op=uninstall&id="+this.id+"&t="+Math.random()+this.iden+"&inajax=1",
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
				alert(e);	
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
* -selected 对话框默认选择的分类
* -mid 模块id
* -idetifier 用于绑定事件的对象标识
* -sum 模块列表最大显示条数
*/

function mode(selected,mid,identifier,sum) {
	this.selected = selected;
	this.mid = mid;
	this.identifier = identifier;
	this.sum = sum;
	this.gids = null;
}

mode.prototype = {
	update:function() {
		var mid = this.mid;
		if(this.gids != '') {
			jq.ajax({
				url:window.location.pathname+"?action=plugins&operation=config&do=7&identifier=bbsgame&pmod=adminmain&act=tpl&classname="+this.mid+"&gids="+this.gids,
				dataType:'json',
				success:function(data){
					try{
						var result = eval(data);
						var invoke_m = jq("#invoke_"+mid);
						var divArea = invoke_m.children(".m_area");
						var loopOuter = document.createElement("UL");
						for(var i in result.games) {
							if(mid == 'float') result.games[i].describe = '';
							var loopInner = document.createElement("LI");
							loopInner.innerHTML = "<img src='"+result.games[i].img+"' /><h3>"+result.games[i].name+"</h3><p>"+result.games[i].describe+"</p>";
							loopOuter.appendChild(loopInner);
						}
						divArea.html(loopOuter.outerHTML);
						invoke_m.css("background","none");
					} catch(e) {
							alert(e);	
					}
				}
			});	
		  }//end if
	}//end update
	,
	bind : function(){
		jq(this.identifier).data("mode",this);
		     jq(this.identifier).click(function() {		
				var mode = jq(this).data("mode");
           	 	Boxy.job("", mode.selected, mode.sum, function(val) {
					var arr = val.split(",");
					if(arr.length>1) {
						for ( var i = 0; i<arr.length; i++ ) {
							arr[i] = arr[i].substr(1);
						}
					}
					else{
						arr[0] = arr[0].substr(1);
					}
					mode.gids = encodeURI(arr);
				 	mode.update();
             	}, {"title":"flash展示列表"});
              	 return false;
        	 });	
	}
}

/*
functions 
*/
function loading(){
		var imgSrc = "/source/plugin/bbsgame/images/waitloadding.gif";
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