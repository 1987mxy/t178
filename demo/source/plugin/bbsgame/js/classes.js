// JavaScript Document
//一级类
function Class(cId, cName) {
    this.classId = cId;
    this.className = cName;
}
//二级类
function Item(iId, iName, cId) {
    this.itemId = iId;
    this.itemName = iName;
    this.classId = cId;
} 

//三级类
function Sub(sId, sName, iId, cId) {
	this.subId = sId;
	this.subName = sName;
	this.itemId = iId;
	this.classId = cId;
}
