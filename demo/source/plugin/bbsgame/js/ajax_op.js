// JavaScript Document
//@require("jquery1.7.min.js");
/**
*
* Author:AgudaZaric
* e-mail:coderzl@hotmail.com
* QQ:384318815
*
**/
var jq = jQuery.noConflict();
/**
* class game
*
* -id ��Ϸid.
*/
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
					alert("��װ���");
					selector.parent().html("��װ���");
					jq("#pw_box").css('display','none');
				}else{
					alert("��װʧ�ܣ�ԭ��:"+arr.msg);
				}
			 }catch(e){
				alert(e);	
			 }	
		 },
		beforeSend:loading()
	});
 },
 uninstall:function() {
	if(confirm('ȷ��Ҫɾ����') == false) return;
	var selector = this.selector;
	var xht = jq.ajax({
		 url:window.location.pathname+"?adminjob=hack&hackset=bbsgame&do=ajax&op=uninstall&id="+this.id+"&t="+Math.random(),
		 dataType:'json',
		 success:function(data){
			 try{
				var arr = eval(data);
				if(arr.ret){
					alert("ж�����");
					selector.parent().html("ж�����");
					jq("#pw_box").css('display','none');
				} else {
					alert("ж��ʧ�ܣ�ԭ��:"+arr.msg);	
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
* -selected �Ի���Ĭ��ѡ��ķ���
* -mid ģ��id
* -idetifier ���ڰ��¼��Ķ����ʶ
* -sum ģ���б������ʾ����
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
				url:window.location.pathname+"?adminjob=hack&hackset=bbsgame&action=setexplorer&do=ajax&op=tpl&mid="+this.mid+"&ids="+this.gids,
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
                	mode.gids = val;
				 	mode.update();
             	}, {"title":"flashչʾ�б�"});
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
		jq("#pw_box").html("<div style='position:absolute; width:150px; height:30px; top:50px; left:"+left+"px; font-size:18px; color:#F3D09B;'>���ڰ�װ���Ե�</div>");
}