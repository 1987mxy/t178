// JavaScript Document
var jq = jQuery.noConflict();
function DialogApp() {
	var Unit = "px";
	var parent = document.getElementById("pkgame_container");
	this.elem =  document.getElementById("dialogelem");

	this.elem.style.top = parseInt(getWindowScrollTop() - parent.offsetTop + 300) + Unit;
	//this.elem.style.left = parseInt((parent.offsetWidth - this.elem.offsetWidth)/2 )+ Unit;

	this.toggle = function() {
		if(this.elem.style.display == "" || this.elem.style.display == "none")
			this.elem.style.display = "block";
		else 
			this.elem.style.display = "none";
	};
	this.hide = function() {
			this.elem.style.display = "none";
	};
	this.show = function() {
			this.elem.style.display = "block";
		
	};
}

function getWindowScrollTop(){
	var scTop;    
    if (typeof window.pageYOffset != 'undefined') {
        scTop = window.pageYOffset;          
    }
    else if (typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') { 
        scTop = document.documentElement.scrollTop;  
    } 
    else if (typeof document.body != 'undefined') { 
        scTop = document.body.scrollTop;   
    }
    return scTop;
}

var pkgameAjax = function(param) {
	var xmlhttp;
	var isDone = false;
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
 	xmlhttp.onreadystatechange=function()
  	{
 	 	if (xmlhttp.readyState==4 && xmlhttp.status==200 && isDone == false)
    	{
    		submitCallBack(xmlhttp.responseText);
			isDone = true;
   		 }
		 else if(xmlhttp.readyState == 3 && isDone == false){
		 	if(xmlhttp.responseText.length > 0)
			{
				submitCallBack(xmlhttp.responseText);
				isDone = true;
			}
		 }
 	 }
	xmlhttp.open("POST", "plugin.php?id=bbsgame:bbsgame&action=set&t="+new Date().getTime(), true);
	xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xmlhttp.send(param);
}

function scoreSubmit(evt) 
{
	var dialog = new DialogApp();
	var host = getHostPath();
	var ext = '';
	if( window.mode ) {
		if( window.pk_id )
			ext = "&mode="+window.mode+"&pk_id="+pk_id;
	}
	if(evt.uid == 0 || evt.uid == 'undefined') {
		dialog.toggle();
		return;
	}
	pkgameAjax("gid="+evt.gid+"&score="+evt.score+"&key="+evt.key+ext);
}

function submitCallBack(data) {
	loading("请稍后，正在努力加载。。。");
	try{
		json2str = data.replace(/\s+jq/g,'');
		var result = eval("data="+json2str);
		setTimeout(hideResult ,5000);
		if(result == null){
			message = "返回空";
		}
		if(result['ret']) 
		{
			message = result['msg'];
			
		} else {
			message = "温馨提示："+result['msg'];
		}
		jq("._popResult").html(message);
		jq("._popResult").show();
	} catch(e) {
		jq("._popResult").html(e);
	}
}

function loading(message){
	new DialogApp().show();
	jq("._popLogin").hide();
	jq("._popResult").html(message);
	jq("._popResult").show();
}

function hideResult() {
	
	jq("._popResult").html('');
	jq("._popLogin").show();
	jq("._popResult").hide();
	new DialogApp().hide();
	return false;
}

function _login(){
		var dialog = new DialogApp();
		var gt = jq("#_uname2").val() == '0' ||typeof jq("#_uname2").val() == 'undefined'?jq("#_uname").val():jq("#_uname2").val();
		var user = jq("#_poppwuser2").val() == '0' || typeof jq("#_poppwuser2").val() == 'undefined'?jq("#_poppwuser").val():jq("#_poppwuser2").val();
		var pwd = jq("#_pwpwd2").val() == '0'||typeof jq("#_pwpwd2").val() == 'undefined'?jq("#_pwpwd").val():jq("#_pwpwd2").val();
		var host = getHostPath();
		if(check(user,pwd) == false)return false;
		
		jq.ajax({
			type:"post",
			url:host+"plugin.php?id=bbsgame:bbsgame&mod=logging&action=login&loginsubmit=yes&infloat=yes&lssubmit=yes&t="+Math.random()*10000,
			data:{"username":user,"password":pwd,"fastloginfield":gt},
			dataType:'json',
			success:function(data){
						try{
							var result = eval(data);
							
							if(result['ret']) 
							{
								window.uid = result['uid'];
								jq("._popLogin").hide();
								dialog.toggle();
							} else {
								alert(result['msg']);
								jq("._popResult").hide();
								jq("._popLogin").show();
							}
							
						} catch(e) {
								alert(e.message);	
						}
						return false;
			},
			error:function(data){
				setTimeout(hideResult ,4000);
				jq("._popResult").html("数据错误");
			},
			beforeSend:loading("请稍后，登录中。。。")
		});
		return false;
}

function check(username,password){

	if(username == "")
	{
		document.getElementById("ustatue").innerHTML= "*空字符串";
		return false;
	} 
	else {
		document.getElementById("ustatue").innerHTML = "";
	}
	if(password == "") 
	{
		document.getElementById("pwstatue").innerHTML = "*空字符串";
		return false;
	}
	else{
		document.getElementById("pwstatue").innerHTML = "";
	}
	return true;
}

function alterBeforePlay(evt) 
{
	if(evt instanceof Object)
	{
		if (!evt.ret)
		{
			if (!evt.uid) {
				loading("<a href='login'>登录</a>后进行游戏可赚取积分");
				setTimeout(function(){hideResult();new DialogApp().show();},2000);
			}
			else if(evt.msg)
			{
				loading(evt.msg);
			}
		}
	}
}

function getGameId() 
{
	return gid;
}

function getApiUrl() 
{
	var host = getHost();
	return host+"source/plugin/bbsgame/nggame.php";
}

function getHostPath()
{
	var reg = /\/.*\//;
	if(reg.exec(window.location.pathname) == null)
		return "/";
	else 
		return reg.exec(window.location.pathname);
}

function getHost(){
	return window.location.host + getHostPath();
}

function getGameUrl(){
	return swf;
}

function getUid() {
	if(typeof(uid) == 'undefined')
		window.uid = 0;
	return uid;
}

function ioErrorHandler(){
	confirm("抱歉，网络原因将导致无法获取积分，确定继续？");
}
var init_dialog = true;
var timer;
var timers;
		
function mov(){
	tim=10;
	timer=setInterval(movs,100);
}
		
function unmov(){
	tim=-10;
	timers=setInterval(movs,100);
}
		
var tim=10;
		
function movs(){
	document.getElementById("moving").scrollTop+=tim;
	if(document.getElementById("moving").scrollTop%100==0){
		clearInterval(timer);
		clearInterval(timers);
	}
}

var challenge = function(){
	
	
	function gnerateDialog() {
		jqhtml = "";
	}
	
	return function() {
		//接受挑战
		this.partake = function(){
			var dialog = new DialogApp();
			var pid = jq("#chpid").val();
			var cnt = jq("#chcnt").val();
			var shared = jq("#chshared").attr('checked');
			var host = getHostPath();
			jq.ajax({
				type:"post",
				url:host+"pkgamepost.php?act=join&action=challenge&time="+Math.random()*10000,
				data:{"pid":pid,"cnt":cnt,"share":shared},
				dataType:'json',
				success:function(data){
							try{
								var result = eval(data);
								
								if(result['ret']) 
								{
									dialog.show();
									message = result['msg'];
									clearTimeout(hideResult);
								} else {
									message = result['msg'];
									setTimeout(hideResult ,5000);
								}
								jq("._popResult").html(message);
								jq("._popResult").show();
								
							} catch(e) {
									alert(e.message);	
							}
							return false;
				},
				error:function(data){
					setTimeout(hideResult ,4000);
					jq("._popResult").html("数据错误");
				}
				//beforeSend:loading("请稍后，努力加载。。。")
			});
			return false;
		};
		
		this.pkAct = function(){
			var def_uid = jq("#def_uid").val();
			var gid = jq("#gid").val();
			var cnt = jq("#chcnt").val();
			var shared = jq("#chshared").attr('checked');
			var c_n = jq("#c_n").val();
			var c_t = jq("#c_t").val();
			var endtime = jq("#endtime").val();
			var host = getHostPath();
			jq.ajax({
				type:"post",
				url:host+"pkgamepost.php?act=add&ajax=1&action=pk&time="+Math.random()*10000,
				data:{"gid":gid,"act_content":cnt,"def_uid":def_uid,"share":shared,"case":c_n,"casetype":c_t,"endtime":endtime},
				dataType:'json',
				success:function(result){
							setTimeout(function(){
								hideResult();
								eval(result.hook);
								},
								3000
							);
							message = result['msg'];
							jq("._popResult").html(message);
							jq("._popResult").show();
							
				},
				error:function(data){
					setTimeout(hideResult ,4000);
					jq("._popResult").html("数据错误909090");
				}
				//beforeSend:loading("请稍后，努力加载。。。")
			});
			return false;
		};
		
	};

}();

function pkgameJoinChallenge(pid,gname,defname,actname) {
	if(defname === actname) {
		alert("抱歉，不能挑战自己发起擂台");	
		return false;
	}
	if (actname == ''){
		alert("请先登录");
		return false;
	}
	var dialog = new DialogApp();
	var url = 'http://'+getHost()+'plugin.php?id=bbsgame:bbsgame&';
	jq("._popResult").html('<div id="challenge">'+
			'<form onSubmit="return new challenge().partake();" method="post">'+
				'<input type="hidden" id="chpid" name="chpid" value="'+pid+'" />'+
				'<p class="challenge_top">输入对TA说的话</p>'+
				'<div class="challenge_cnt">'+
					'<p style="margin-bottom:5px;">'+
					'<textarea id="chcnt" name="chcnt">#打擂台# '+actname+'对'+defname+'发起挑战,挑战游戏《'+gname+'》。'+url+'</textarea></p>'+
					'<p>'+
						'<span class="btn2" style="margin:0 10px 0 0;"> '+
						'<span><button type="submit">提交</button></span></span>&nbsp;&nbsp;'+
						'<span class="btn2" style="margin:0 10px 0 0;"><span><button onclick="new DialogApp().hide();" type="button">取消</button></span></span>&nbsp;'+
					'<input id="chshared" name="share" type="checkbox" checked="checked">分享到个人动态'+
				'</p></div>'+
			'</form></div>');
	jq("._popLogin").hide();
	jq("._popResult").show();
	dialog.show();
	return false;
}

function pkgamePkSomeOne(def_uid, def_name, act_uid, gid, gname) {
	if(act_uid == '' || act_uid == 0) {
		alert("抱歉，您还没登录");
		return false;
	}
	if(def_uid === act_uid) {
		alert("抱歉，不能挑战自己喔");	
		return false;
	}
	var options = '';
	if( window.c_cnf != null) {
		for( var i in c_cnf ) 
		{
			options += "<option value="+c_cnf[i].key+">"+c_cnf[i].value+"</option>";
		}
	}
	
	var dialog = new DialogApp();
	jq("._popResult").html('<div id="challenge">'+
				'<form onSubmit="return new challenge().pkAct();" method="post">'+
				'<input type="hidden" id="gid" name="gid" value="'+gid+'" />'+
				'<input type="hidden" id="def_uid" name="def_uid" value="'+def_uid+'" />'+
				'<p class="challenge_top">输入对TA说的话</p><div class="challenge_cnt">'+
				'<p style="margin-bottom:5px;"><textarea id="chcnt" name="chcnt">#下战书# 对 @'+def_name+' 发起挑战《'+gname+'》.大家都叫我奥特曼，你敢来与我比试吗？</textarea></p>'+
				'<p style="margin-bottom:10px; font-size:12px;">失败者被扣除：<input style="width:30px" type="text" name="c_n" id="c_n"><select name="c_t" id="c_t">'+options+'</select>奖励给赢家。</p>'+
				'<p style="margin-bottom:10px; font-size:12px;">结束时间：<input onfocus="HS_setDate(this)" style="width:60px" type="text" name="endtime" id="endtime"></p>'+
				'<p><span class="btn2" style="margin:0 10px 0 0;"> '+
					'<span><button type="submit">提交</button></span></span>&nbsp;&nbsp;<span class="btn2" style="margin:0 10px 0 0;"><span><button onclick="new DialogApp().hide();" type="button">取消</button></span></span>&nbsp;'+
					'<input id="chshared" name="share" type="checkbox" checked="checked">分享到个人动态</p>'+
				'</div></form></div>');
	jq("._popLogin").hide();
	jq("._popResult").show();
	dialog.show();
	return false;
}