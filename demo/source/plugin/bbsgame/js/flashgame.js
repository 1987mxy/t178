// JavaScript Document
var jq = jQuery.noConflict();
lastScrollY=0;
function heartBeat(neryid) { 
	var diffY;
	if (document.documentElement && document.documentElement.scrollTop)
		diffY = document.documentElement.scrollTop;
	else if (document.body)
		diffY = document.body.scrollTop
	else
		{/*Netscape stuff*/}    
	percent=.1*(diffY-lastScrollY); 
	if(percent>0)percent=Math.ceil(percent); 
	else percent=Math.floor(percent); 
	document.getElementById(neryid).style.top=parseInt(document.getElementById(neryid).style.top)+percent+"px";
	lastScrollY=lastScrollY+percent;
}

function hidethis(neryid){
	document.getElementById(neryid).style.visibility="hidden";
}

/**
 * flash弹出
 **/
function openLayer(obj,objId,conId,gameId){
	window.swf = floatGames[gameId].swf;
	window.gid = gameId;
	//window.dialogpid
	var arrayPageSize   = getPageSize();//调用getPageSize()函数
	var arrayPageScroll = getPageScroll();//调用getPageScroll()函数
	if (!document.getElementById("popupAddr")){
		//创建弹出内容层
		var popupDiv = document.createElement("div");
		//给这个元素设置属性与样式
		popupDiv.setAttribute("id","popupAddr")
		popupDiv.style.position = "absolute";
		popupDiv.style.border = "1px solid #ccc";
		popupDiv.style.background = "#fff";
		popupDiv.style.zIndex = 99;
		//创建弹出背景层
		var bodyBack = document.createElement("div");
		bodyBack.setAttribute("id","bodybg");
		bodyBack.style.position = "absolute";
		bodyBack.style.width = "100%";
		bodyBack.style.height = (arrayPageSize[1] + 35 + 'px');
		bodyBack.style.zIndex = 98;
		bodyBack.style.top = 0;
		bodyBack.style.left = 0;
		bodyBack.style.filter = "alpha(opacity=70)";
		bodyBack.style.opacity = 0.7;
		bodyBack.style.background = "#000";
		//实现弹出(插入到目标元素之后)
		var mybody = document.getElementById(objId);
		insertAfter(popupDiv,mybody);//执行函数insertAfter()
		insertAfter(bodyBack,mybody);//执行函数insertAfter()
	}
	//显示背景层
	document.getElementById("bodybg").style.display = "";
	//显示内容层
	var popObj=document.getElementById("popupAddr");
	popObj.innerHTML = document.getElementById(conId).innerHTML;
	popObj.style.display = "";
	//让弹出层在页面中垂直左右居中(统一)
	// popObj.style.width  = "600px";
	// popObj.style.height = "400px";
	// popObj.style.top  = arrayPageScroll[1] + (arrayPageSize[3] - 35 - 400) / 2 + 'px';
	// popObj.style.left = (arrayPageSize[0] - 20 - 600) / 2 + 'px';
	//让弹出层在页面中垂直左右居中(个性)
	var arrayConSize=getConSize(conId);
	popObj.style.top  = (document.documentElement.clientHeight-650)/2+"px";
	popObj.style.left = (document.body.clientWidth-850)/2+"px";
	var Popflash = document.getElementById("floatflash");
	document.getElementById("flashhere").innerHTML = flashObject(floatGames[gameId].shell);
	window.scrollTo(0,0);
	document.body.style.overflowY="hidden";
	jq("#closediv").click(function(){ 
			jq("#bodybg").hide();
			jq("#nOpen").hide();
	});
	new floatGame().getRelatives(gid);
}

	function flashObject(swf) {
		return "<object id='floatflash'  classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0' width='640' height='480' id='pigPark' align='middle'><param name='allowScriptAccess' value='always'/><param name='allowFullScreen' value='false' /><param name='movie' value='"+swf+"' /><param name='quality' value='high' /> <param name='wmode' value='transparent' /><embed src='"+swf+"' quality='high' width='640' height='480' wmode='transparent' name='embedname' align='middle' allowScriptAccess='always' allowFullScreen='false' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' /></object>";
	}
	
	//获取内容层内容原始尺寸
	function getConSize(conId){
		var conObj=document.getElementById(conId)
		conObj.style.position = "absolute";
		conObj.style.left=-1000+"px";
		conObj.style.display="";
		var arrayConSize=[conObj.offsetWidth,conObj.offsetHeight]
		conObj.style.display="none";
		return arrayConSize;
	}
	
	function insertAfter(newElement,targetElement){//插入
		var parent = targetElement.parentNode;
		if(parent.lastChild == targetElement){
			parent.appendChild(newElement);
		}
		else{
			parent.insertBefore(newElement,targetElement.nextSibling);
		}
	}

//获取滚动条的高度
function getPageScroll(){
	var yScroll;
	if (self.pageYOffset) {
	yScroll = self.pageYOffset;
	} else if (document.documentElement  &&  document.documentElement.scrollTop){
	yScroll = document.documentElement.scrollTop;
	} else if (document.body) {
	yScroll = document.body.scrollTop;
	}
	arrayPageScroll = new Array('',yScroll)
	return arrayPageScroll;
}

//获取页面实际大小
function getPageSize(){
	var xScroll,yScroll;
	if (window.innerHeight  &&  window.scrollMaxY){
	xScroll = document.body.scrollWidth;
	yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){
	sScroll = document.body.scrollWidth;
	yScroll = document.body.scrollHeight;
	} else {
	xScroll = document.body.offsetWidth;
	yScroll = document.body.offsetHeight;
	}
	var windowWidth,windowHeight;
	if (self.innerHeight) {
	windowWidth = self.innerWidth;
	windowHeight = self.innerHeight;
	} else if (document.documentElement  &&  document.documentElement.clientHeight) {
	windowWidth = document.documentElement.clientWidth;
	windowHeight = document.documentElement.clientHeight;
	} else if (document.body) {
	windowWidth = document.body.clientWidth;
	windowHeight = document.body.clientHeight;
	}
	var pageWidth,pageHeight
	if(yScroll < windowHeight){
	pageHeight = windowHeight;
	} else {
	pageHeight = yScroll;
	}
	if(xScroll < windowWidth) {
	pageWidth = windowWidth;
	} else {
	pageWidth = xScroll;
	}
	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight)
	return arrayPageSize;
}

//关闭弹出层
function closeLayer(){
	document.body.style.overflowY="";
	document.getElementById("popupAddr").style.display = "none";
	document.getElementById("bodybg").style.display = "none";
	return false;
}

var floatGame = function(jq,url){
	
	var data ;
	function getApi(){
		return getHostPath()+url;
	};
	
	function getHostPath(){
		var reg = /\/.*\//;
		if(reg.exec(window.location.pathname) == null)
			return "/";
		else 
			return reg.exec(window.location.pathname);
	}
	
	function requestResult(gid){
		jq.ajax({
			type:"get",
			url:getApi()+"?s=rel&gid="+gid, 
			dataType:'json',
			success:function(data) {view(data);}
		});
	}
	
	function view(data) {
		jq("#f_operation").html(data.game.creditdesc);
		jq("#g_title").html(data.game.name);
		var trs = new Array();
		var rels = data.relatives;
		for(var i in rels) {
			if(floatGames.hasOwnProperty(rels[i].gid) === false) floatGames[rels[i].gid] = {name:rels[i].name,shell:rels[i].shell,swf:rels[i].swf};
			var elem = '<tr><td style="text-align:center; margin:0; padding:0">'+
					   '<img onError="this.src=\'source/plugin/bbsgame/images/404/404_tips.png\'" onclick="openLayer(this,\'test3\',\'test_con3\','+rels[i].gid+')" src="'+rels[i].img+'" width="77" height="78" /><div '+
					   'style="font-size:12px; line-height:22px;">"'+rels[i].name+'"</div></td></tr>';
			trs.push(elem)
		}
		var inner = '<table style="width:115px;">'+trs.join('')+'</table>';
		jq("#loading").html(inner);
	}
	
	function getResult(){
		requestResult(gid);
	}
	
	return function(){
		
		this.getRelatives = function(gid){
			requestResult(gid);
		};

	};
			
}(jQuery||{},"hack/bbsgame/pkwtf.php")