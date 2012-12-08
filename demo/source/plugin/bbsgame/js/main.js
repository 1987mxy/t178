// JavaScript Document
	var jq = jQuery.noConflict();
	function  change_games(num,obj){
		switch(num){
				case 1:
					obj.className="ggame_li2";
					jq('#ggame_li2').attr("class", "ggame_li1");
					jq('#ggame_li3').attr("class", "ggame_li1");
					jq('#ggame_li4').attr("class", "ggame_li1");
					jq('#jf_con1').css("display","");
					jq('#jf_con2').css("display","none");
					jq('#jf_con3').css("display","none");
					jq('#jf_con4').css("display","none");
					break;
				case 2:
					obj.className="ggame_li2";
					jq('#ggame_li1').attr("class", "ggame_li1");
					jq('#ggame_li3').attr("class", "ggame_li1");
					jq('#ggame_li4').attr("class", "ggame_li1");
					jq('#jf_con1').css("display","none");
					jq('#jf_con2').css("display","");
					jq('#jf_con3').css("display","none");
					jq('#jf_con4').css("display","none");
					break;
				case 3:
					obj.className="ggame_li2";
					jq('#ggame_li2').attr("class", "ggame_li1");
					jq('#ggame_li1').attr("class", "ggame_li1");
					jq('#ggame_li4').attr("class", "ggame_li1");
					jq('#jf_con1').css("display","none");
					jq('#jf_con2').css("display","none");
					jq('#jf_con3').css("display","");
					jq('#jf_con4').css("display","none");
					break;
				case 4:
					obj.className="ggame_li2";
					jq('#ggame_li2').attr("class", "ggame_li1");
					jq('#ggame_li3').attr("class", "ggame_li1");
					jq('#ggame_li1').attr("class", "ggame_li1");
					jq('#jf_con1').css("display","none");
					jq('#jf_con2').css("display","none");
					jq('#jf_con3').css("display","none");
					jq('#jf_con4').css("display","");
					break;
					case 0:
				
			}
	}

	function change_Pkgames(num,obj){
		switch(num){
			case 1:
				obj.className="kp_xx";
				jq("#jf_con1").css("display","");
				jq("#jf_con2").css("display","none");
				jq("#jf_con3").css("display","none");
				jq("#jf_con4").css("display","none");
				
				jq("#kp2").attr("class","kp_mj");
				jq("#kp3").attr("class","kp_mj");
				jq("#kp4").attr("class","kp_mj");
			break;
			case 2:
				obj.className="kp_xx";
				jq("#jf_con1").css("display","none");
				jq("#jf_con2").css("display","");
				jq("#jf_con3").css("display","none");
				jq("#jf_con4").css("display","none");
				
				jq("#kp1").attr("class","kp_mj");
				jq("#kp3").attr("class","kp_mj");
				jq("#kp4").attr("class","kp_mj");
			break;
			case 3:
				obj.className="kp_xx";
				jq("#jf_con1").css("display","none");
				jq("#jf_con2").css("display","none");
				jq("#jf_con3").css("display","");
				jq("#jf_con4").css("display","none");
				jq("#kp2").attr("class","kp_mj");
				jq("#kp1").attr("class","kp_mj");
				jq("#kp4").attr("class","kp_mj");
			break;
			case 4:
				obj.className="kp_xx";
				jq("#jf_con1").css("display","none");
				jq("#jf_con2").css("display","none");
				jq("#jf_con3").css("display","none");
				jq("#jf_con4").css("display","");
				
				jq("#kp2").attr("class","kp_mj");
				jq("#kp3").attr("class","kp_mj");
				jq("#kp1").attr("class","kp_mj");
			break;
			case 0:
				obj.className="kp_xxs";
			break;
		}
	}
	
	function unchange_Pkgames(obj){
		obj.className="kp_more";
	}

	function  change_games2(num,obj){
		switch(num){
				case 1:
					obj.className="ggame_li2";
					jq('#ggame_li12').attr("class", "ggame_li1");
					jq('#ggame_li13').attr("class", "ggame_li1");
					jq('#ggame_li14').attr("class", "ggame_li1");
					
					jq('#xxyx').css("display","");
					jq('#mjyx').css("display","none");
					jq('#yzyx').css("display","none");
					jq('#clyx').css("display","none");
					break;
				case 2:
					obj.className="ggame_li2";
					jq('#ggame_li11').attr("class", "ggame_li1");
					jq('#ggame_li13').attr("class", "ggame_li1");
					jq('#ggame_li14').attr("class", "ggame_li1");
					jq('#xxyx').css("display","none");
					jq('#mjyx').css("display","");
					jq('#yzyx').css("display","none");
					jq('#clyx').css("display","none");
					break;
				case 3:
					obj.className="ggame_li2";
					jq('#ggame_li12').attr("class", "ggame_li1");
					jq('#ggame_li11').attr("class", "ggame_li1");
					jq('#ggame_li14').attr("class", "ggame_li1");
					jq('#xxyx').css("display","none");
					jq('#mjyx').css("display","none");
					jq('#yzyx').css("display","");
					jq('#clyx').css("display","none");
					break;
				case 4:
					obj.className="ggame_li2";
					jq('#ggame_li12').attr("class", "ggame_li1");
					jq('#ggame_li13').attr("class", "ggame_li1");
					jq('#ggame_li11').attr("class", "ggame_li1");
					jq('#xxyx').css("display","none");
					jq('#mjyx').css("display","none");
					jq('#yzyx').css("display","none");
					jq('#clyx').css("display","");
					break;
				
			}
	}

	function renqiph(num,obj){
			switch(num){
				case 1:
					obj.className="game_pk_zn";
					jq('#game_ph').attr("class","game_pk_zn");
					jq('#game_zn').attr("class","game_pk_zph");
					jq('#ul2').css("display","");
					jq('#ul1').css("display","none");
				break;
				case 2:
					obj.className="game_pk_zph";
					jq('#game_ph').attr("class","game_pk_zph");
					jq('#game_zn').attr("class","game_pk_zn");
					jq('#ul2').css("display","none");
					jq('#ul1').css("display","");
				break;
			}
		}

	function change_tz(obj){
		obj.style.cursor="pointer";
		obj.src="source/plugin/bbsgame/images/1/n_games_44.jpg";
	}
	function unchange_tz(obj){
		obj.src="source/plugin/bbsgame/images/1/n_games_35.jpg";
	}
	function change_tzs(obj){
		obj.style.cursor="pointer";
		obj.src="source/plugin/bbsgame/images/1/n_games_44.jpg";
	}
	function unchange_tzs(obj){
		obj.src="source/plugin/bbsgame/images/2/jf_win_15.jpg";
	}
	function zhannei(obj){
		jq("#zn_ph").attr("class","zn_ph");
		jq("#z_ph").attr("class","z_ph");
		jq("#paihang1").css("display","");
		jq("#paihang2").css("display","none");
		jq("#last1").css("display","");
		jq("#last2").css("display","none");
	}
	function zpai(obj){
		jq("#zn_ph").attr("class","z_ph");
		jq("#z_ph").attr("class","zn_phs");
		
		jq("#paihang1").css("display","none");
		jq("#paihang2").css("display","");
		jq("#last1").css("display","none");
		jq("#last2").css("display","");
	}
	function change_mingames(num,obj){
			obj.className="kp_xx";
			switch(num){
				case 1:
					jq("#clyx1").css("display","");
					jq("#clyx2").css("display","none");
					jq("#clyx3").css("display","none");
					jq("#clyx4").css("display","none");
					
					jq("#kp12").attr("class","min_mj");
					jq("#kp13").attr("class","min_mj");
					jq("#kp14").attr("class","min_mj");
				break;
				case 2:
					jq("#clyx1").css("display","none");
					jq("#clyx2").css("display","");
					jq("#clyx3").css("display","none");
					jq("#clyx4").css("display","none");
					
					jq("#kp11").attr("class","min_mj");
					jq("#kp13").attr("class","min_mj");
					jq("#kp14").attr("class","min_mj");
				break;
				case 3:
					jq("#clyx1").css("display","none");
					jq("#clyx2").css("display","none");
					jq("#clyx3").css("display","");
					jq("#clyx4").css("display","none");
					
					jq("#kp12").attr("class","min_mj");
					jq("#kp11").attr("class","min_mj");
					jq("#kp14").attr("class","min_mj");
				break;
				case 4:
					jq("#clyx1").css("display","none");
					jq("#clyx2").css("display","none");
					jq("#clyx3").css("display","none");
					jq("#clyx4").css("display","");
					
					jq("#kp12").attr("class","min_mj");
					jq("#kp13").attr("class","min_mj");
					jq("#kp11").attr("class","min_mj");
				break;
				
			}
	}
	
var s=0;
function timer(){
	var imgs = jq("#top_ad_change").children("img");
	var len = imgs.length;
	for(var i = 0; i < len; i++) {
		imgs[i].style.display = 'none';
	}
	imgs[s].style.display = 'inline';
	s++;
	if(s>1)
	{
		s=0;
	}
}

var checkCss = function()
{
	jq("#pk_jf").find("._bolder_up").css("width","");
}


var sngModule = function(jq,url){
	var endtime;
	
	function getHostPath(){
		var reg = /\/.*\//;
		if(reg.exec(window.location.pathname) == null)
			return "/";
		else 
			return reg.exec(window.location.pathname);
	}
	
	var num = 0,snum = 0,speed = 100;
	
	function Marquee2(){
	  		if(document.getElementById("link_logo2").offsetTop-document.getElementById("link_logo").scrollTop<=snum)
	     	{
			 	document.getElementById("link_logo").scrollTop-=document.getElementById("link_logo1").offsetHeight;
		 	}
		 	else
		 	{
		    	document.getElementById("link_logo").scrollTop++
			}
			if(num==document.getElementById("link_logo").scrollTop)
			{
				snum=document.getElementById("link_logo2").offsetTop-num;
				num=0;
			}
			num++;
	}

	
	window.pkTime = function(){
		time = endtime*1000-new Date().getTime();
		if(time <= 0) time = 0;
		jq("#pktime").html('剩余时间：<span class="jf_font">'+Math.floor(time/3600000)+'</span>小时<span class="jf_font">'+Math.floor(time%3600000/60000)+'</span>分<span class="jf_font">'+Math.floor(time%3600000%60000/1000)+'</span>秒');	
	}
	
	function _getJLList(){
		jq.ajax({
			type:"get",
			url:getApi()+"?s=pkranking", 
			dataType:'json',
			success:function(result) { 
				var elem = '<ul>';
				var count = result.length;
				for(var i=0;i<count;i++) {
					var time = result[i].awdTime;
					var tips = result[i].awdWhat;
					elem += ' <li><div class="jf_fonts"><span class="pk_timer">'+time+'</span>'+tips+'</div></li>';
				}
				elem +='</ul>';
				jq("#pkking_content").append(elem);
			 }
		});	
	}
		
	function getApi(){
		return getHostPath()+url;
	};
	
	return function(){	
	
		this.getApi = function(){
			return getApi();
		};
		
		this.getPk = function() {
				jq.ajax({
					type:"get",
					url:this.getApi()+"?s=pk", 
					dataType:'json',
					success:function(result) { 
						endtime = parseInt(result.endTime); 
						time = new Date(endtime-new Date().getTime());
						var elem = '<div class="pkk_con"><img src="'+result.gameUrl+'" width="65" height="65" style="float:left;" /><div style="float:left; width:100px; margin-left:10px; mar"><span style="font-size:16px; line-height:20px; font-weight:600">'+result.topgameName+'</span><br /><span style="font-size:12px; line-height:20px;">玩家：'+result.topUser+'</span><div class="clearfloat"></div><span class="jf_fonts">分数：<span class="jf_font">'+result.topScore+'</span> 分</span></div><div class="clearfloat"></div></div><div style="text-align:center; margin:15px 10px 0 10px"><a href="plugin.php?id=bbsgame&action=game&gid='+result.gameId+'"><img src="source/plugin/bbsgame/images/1/n_games_41.jpg" width="122" height="35" /></a></div><div class="jf_font_time" id="pktime" style="text-align:center; width:100%"></div>';
						jq("#pkDiv").append(elem);
						_getJLList();
						setInterval(pkTime,1000);
					 }
				});
		};
		
		this.getXyx = function(cid){
				if(cid == '') return false;
				jq("div [id*=clyx]").each(function(){
					jq(this).hide();	
				});
				jq(".mini_title [id*=kp1]").each(function(){
					jq(this).attr("class",'kp_mj');
				});
				jq("#kp1"+cid).attr("class",'kp_xx');
				if(jq("#clyx"+cid).length != 0){
					jq("#clyx"+cid).show();
					return false;
				}
				jq.ajax({
					type:"get",
					url:this.getApi()+"?s=xyx&c="+cid, 
					dataType:'json',
					success:function(data) {
						data = eval(data);
						var elem = '<div class="jifen_xcontent" style="margin-top:10px;" id="clyx'+cid+'">';
						for(var i in data.result){
							elem += '<div class="jf_ximg"><a href="plugin.php?id=bbsgame&action=game&gid='+data.result[i].gid+'&step=1&swf='+data.result[i].swf+'&name='+data.result[i].name+'&gid='+data.result[i].gid+'"><img onError="this.src=\'source/plugin/bbsgame/images/404/404_tips.png\'" alt="'+data.result[i].name+'" src="'+data.result[i].img+'" width="77" height="78" /></a> <div style="line-height:24px;">'+data.result[i].name+'</div></div>';				
							if((parseInt(i)+1)%7==0 && parseInt(i)!=13)
								elem +='<div class="clearfloat"></div><div style="width:650px; height:1px; background-image:url(source/plugin/bbsgame/images/1/xuxian_07.jpg); background-repeat:repeat-x"></div>';
							if(parseInt(i)==13)
								elem +=' <div style="clear:both; float:none"></div>';
						}
						jq("#xyxcnt").append(elem);
					}

				});
		};
		
		
		this.getRq = function() {
			var count = 10;
			jq.ajax({
				type:"get",
				url:this.getApi()+"?s=recmd&t="+count, 
				dataType:'json',
				success:function(result) { 
					try{
						if(result == '' || result == null) return;
						var elem = '';
						var style = '';
						count = result.list.length;
						for( var i=0; i<count; i++ ){
							if( i<3 ){
								elem += '<div class="rank_'+(i+1)+'"><span class="pos"><img src="source/plugin/bbsgame/images/1/'+(i+1)+'.gif" class="rank_num1" /></span><span class="rank_font_top3"><img class="rank_img" src="'+result.list[i].gimg+'" width=30 >'+result.list[i].gname+'</span><span class="dk_num"><font color="red">'+result.list[i].num+'</font>人</span> </div>';
								if( i==2 || i == count-1 ){
									jq('#PHtop3').html('');
									jq('#PHtop3').append(elem);
									elem = '';
								}
							}
							else{
								if(i == count-1 ) style = 'style="border-bottom:none"';
								elem += '<div class="rank_all" '+style+'><span class="pos"><img src="source/plugin/bbsgame/images/1/'+(i+1)+'.gif" class="rank_num3"/></span><span class="rank_font_top3"><img class="rank_img" src="'+result.list[i].gimg+'" width=30 >'+result.list[i].gname+'</span><span class="dk_num"><font color="red">'+result.list[i].num+'</font>人</span> </div>';
								if( i==count-1 ){
									jq("#last2").html('');
									jq("#last2").append(elem);
								}
							}
						}
					}
					catch(e){
						//alert(e);
					}
				}
			});	
		};
		
		this.getIv = function(){
			jqoption = {
					type:"get",
					"url":this.getApi()+"?s=ivSet",
					dataType:'json',
					success:function(result){
						jq('#ivset').html(result);
					}
				};
			
			jq.ajax(jqoption);
		};

		this.getUpdateNotice = function(){
			jq.ajax({
				'url':"source/plugin/bbsgame/pkwtf.php?s=checkUpdate&t="+new Date().getTime(),
				'type':'get',
				'dataType':'json',
				'success':function(data){
					if(data.needUpdate)
					{
						window.exp = data.exp;
						jq("#pkgame_notice").html('<script type="text/javascript" src="'+data.jsSrc+'"></script>');
					}
				}
			});
		};
	};
}(jQuery||{},"source/plugin/bbsgame/pkwtf.php");


var tplModule = function (parentModule){
	var parentModule = typeof(parentModule) == 'function' ? new parentModule : parentModule;
	parentModule.start = function(){
		parentModule.getPk();
	};
	return parentModule;
}(sngModule||{});


var contentModule = function (parentModule){
	var parentModule = typeof(parentModule) == 'function' ? new parentModule : parentModule;
	var resLen = 0;
	var temp = [0,4];
	function view(result){
		resLen = result.length;
		var elem = '';
		for(var i=1; i<resLen; i++) {
			var theOne = '';
			if(i<=3) theOne = 't_ones';
			elem += '<tr class="tr_list t_mcs '+theOne+'">'+
					'<td style="width:25%; text-align:center; padding:5px 0">'+i+'</td>'+
					'<td style="width:25%; text-align:center;"><div class="td_name">'+result[i].nickName+'</td>'+
					'<td style="width:25%;  text-align:center;">'+result[i].score+'</div></td></tr>';
		}
		jq("#ph_tb2").append(elem);
	}
	
	
	
	return function(){
		this.ranking = function(gameid, gamename, activeFlag) {
			if(gameid =='') return;
			jq.ajax({
				type:"get",
				url:parentModule.getApi()+"?s=topscore&gameid="+gameid+"&gamename="+gamename+"&activeFlag="+activeFlag, 
				dataType:'json',
				success:function(result){
					view(result);	
				}
			});	
		};

		this.getIv = function()
		{
			parentModule.getIv();
		};

		this.scrollPkList = function(dire){
			if(dire == 'up') {
				if(temp[0]<5){
					temp[0] = 0;
					temp[1] = temp[0]+4;
				}else{
					temp[0] -= 5;
					temp[1] -= 5;		
				}
			}
			else{
				if(temp[1]>resLen-5){
					temp[1] = resLen-1;
					temp[0] = temp[1]-4;
				}else{
					temp[0] += 5;
					temp[1] += 5;
				}
			}
			if(jq("#_zns[class=rank_zns]").length != 0 ) {
				jq(".tr_list").each(function(i){
					if(i>=temp[0] && i<=temp[1]){
						jq(this).css("display","");
					}
					else {
						jq(this).css("display","none");
					}
				});
			}
			else{
				jq(".tr_ilist").each(function(i){
					if(i>=temp[0] && i<=temp[1]){
						jq(this).css("display","");
					}
					else {
						jq(this).css("display","none");
					}
				});
			}
		};
	};
}(sngModule||{});


/**
* 添加click事件绑定到dom对象，并根据id寻找显示dom.隐藏其他。
* 格式要求：keyword a_b 展现dom id a_b_onload 隐藏dom id a_*_onload
* -keyword 监听对象id
* -jq jQuery
*/
var initEventListener = function (keyword, jq, event) {
	
	if(keyword == '' || typeof keyword !== 'string' || jq == null || typeof jq !== 'function') return;
	var matches = keyword.match(/([^_]*)_([^_]*)/);
	if(matches[1] == '' || matches[2] == '') return;
	var listnerDomId = keyword;
	var actionDomId = keyword+"_onload";
	jq("#"+listnerDomId).click(function(){
		jq('div [id^="'+matches[1]+'"]').each(function(){
			jq(this).removeClass(matches[1]+'_selected');
		});
	
		jq(this).addClass(matches[1]+'_selected');
		jq('[id^="'+matches[1]+'"][id$="onload"]').each(function(){
			jq(this).hide();
		});
		jq("#"+keyword+"_onload").show();
		if(typeof event !== 'undefined') {
			for(var i in event) {
				a = event[i].obj;
				b = event[i].fn;
				eval(a.b);
			}
		}
	});
}
	
	
function getHostPath(){
	var reg = /\/.*\//;
	if(reg.exec(window.location.pathname) == null)
		return "/";
	else 
		return reg.exec(window.location.pathname);

}

function getHost(){
	return window.location.host + getHostPath();
}

function replylogin(){
	new DialogApp().show();
	void(0);
}
function clipBoard(classname) {
	
	jq("."+classname).click(function(){
		var ab = jq(this).prev("textarea").val();
		if (document.all){                                            //判断Ie
			window.clipboardData.setData('text', ab);
			alert("复制成功");
		}else{
			jq(this).prev("textarea").focus();
			alert("您的浏览器不支持剪贴板操作，请ctrl+c自行复制。"); 
		}
	});
}

function signIn(){

	jq.ajax({
		type:"post",
		url:"source/plugin/bbsgame/pkwtf.php?sign=1",
		dataType:'json',
		success:function(data){
			//new DialogApp().show();
			loading(data.msg);
		},
		error:function(data){
			loading(data);
		}
	});
	return false;
}

/**
 * Figure out how long it takes for a method to execute.
 * 
 * @param {func} method to test 
 * @param {int} iterations number of executions.
 * @param {Array} args to pass in. 
 * @param {T} context the context to call the method in.
 * @return {int} the time it took, in milliseconds to execute.
 */
var bench = function (method, iterations, args, context) {

    var time = 0;
    var timer = function (action) {
        var d = +(new Date);
        if (time < 1 || action === 'start') {
            time = d;
            return 0;
        } else if (action === 'stop') {
            var t = d - time;
            time = 0;    
            return t;
        } else {
            return d - time;    
        }
    };

    var result = [];
    var i = 0;
    timer('start');
    while (i < iterations) {
        result.push(method.apply(context, args));
        i++;
    }

    var execTime = timer('stop');

    if ( typeof console === "object") {
        console.log("Mean execution time was: ", execTime / iterations);
        console.log("Sum execution time was: ", execTime);
        console.log("Result of the method call was:", result[0]);
    }

    return execTime;  
};


function changeVcode(sth){
	sth.src = 'source/plugin/bbsgame/pkwtf.php?vcode=vcode&notime='+new Date().getTime();
}

function netgame_slider(){
		if(netgameId == 1)
			jq("#pkgame_netgame_a").click();
		else
			jq("#pkgame_netgame_b").click();
		netgameId *= -1;
}