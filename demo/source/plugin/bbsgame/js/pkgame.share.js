// JavaScript Document
(function(){
  var _w = 24 , _h = 24;
  var param = {
    url:location.href,
    type:'2',
    count:'', /**�Ƿ���ʾ��������1��ʾ(��ѡ)*/
    appkey:'', /**�������Ӧ��appkey,��ʾ������Դ(��ѡ)*/
    title:'', /**�������������(��ѡ��Ĭ��Ϊ����ҳ���title)*/
    pic:'', /**����ͼƬ��·��(��ѡ)*/
    ralateUid:'', /**�����û���UID������΢����@���û�(��ѡ)*/
	language:'zh_cn', /**�������ԣ�zh_cn|zh_tw(��ѡ)*/
    rnd:new Date().valueOf()
  }
  var temp = [];
  for( var p in param ){
    temp.push(p + '=' + encodeURIComponent( param[p] || '' ) )
  }
  document.write('<iframe allowTransparency="true" frameborder="0" scrolling="no" src="http://hits.sinajs.cn/A1/weiboshare.html?' + temp.join('&') + '" width="'+ _w+'" height="'+_h+'"></iframe>')
})();
