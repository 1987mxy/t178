/**
 * Boxy 0.1.4 - Facebook-style dialog, with frills
 *
 * (c) 2008 Jason Frame
 * Licensed under the MIT License (LICENSE)
 */
 
/*
 * jQuery plugin
 *
 * Options:
 *   message: confirmation message for form submit hook (default: "Please confirm:")
 * 
 * Any other options - e.g. 'clone' - will be passed onto the boxy constructor (or
 * Boxy.load for AJAX operations)
 */
 var jq = jQuery.noConflict();
jQuery.fn.boxy = function(options) {
    options = options || {};
    return this.each(function() {      
        var node = this.nodeName.toLowerCase(), self = this;
        if (node == 'a') {
            jQuery(this).click(function() {
                var active = Boxy.linkedTo(this),
                    href = this.getAttribute('href'),
                    localOptions = jQuery.extend({actuator: this, title: this.title}, options);
                    
                if (active) {
                    active.show();
                } else if (href.indexOf('#') >= 0) {
                    var content = jQuery(href.substr(href.indexOf('#'))),
                        newContent = content.clone(true);
                    content.remove();
                    localOptions.unloadOnHide = false;
                    new Boxy(newContent, localOptions);
                } else { // fall back to AJAX; could do with a same-origin check
                    if (!localOptions.cache) localOptions.unloadOnHide = true;
                    Boxy.load(this.href, localOptions);
                }
                
                return false;
            });
        } else if (node == 'form') {
            jQuery(this).bind('submit.boxy', function() {
                Boxy.confirm(options.message || '请确认：', function() {
                    jQuery(self).unbind('submit.boxy').submit();
                });
                return false;
            });
        }
    });
};

//
// Boxy Class

function Boxy(element, options) {
    
    this.boxy = jQuery(Boxy.WRAPPER);
    jQuery.data(this.boxy[0], 'boxy', this);
    
    this.visible = false;
    this.options = jQuery.extend({}, Boxy.DEFAULTS, options || {});
    
    if (this.options.modal) {
        this.options = jQuery.extend(this.options, {center: true, draggable: false});
    }
    
    // options.actuator == DOM element that opened this boxy
    // association will be automatically deleted when this boxy is remove()d
    if (this.options.actuator) {
        jQuery.data(this.options.actuator, 'active.boxy', this);
    }
    
    this.setContent(element || "<div></div>");
    this._setupTitleBar();
    
    this.boxy.css('display', 'none').appendTo(document.body);
    this.toTop();

    if (this.options.fixed) {
        if (jQuery.browser.msie && jQuery.browser.version < 7) {
            this.options.fixed = false; // IE6 doesn't support fixed positioning
        } else {
            this.boxy.addClass('fixed');
        }
    }
    
    if (this.options.center && Boxy._u(this.options.x, this.options.y)) {
        this.center();
    } else {
        this.moveTo(
            Boxy._u(this.options.x) ? this.options.x : Boxy.DEFAULT_X,
            Boxy._u(this.options.y) ? this.options.y : Boxy.DEFAULT_Y
        );
    }
    
    if (this.options.show) this.show();

};


Boxy.EF = function() {};

jQuery.extend(Boxy, {

    WRAPPER: "<table cellspacing='0' cellpadding='0' border='0' class='boxy-wrapper'>" +
                "<tr><td class='top-left'></td><td class='top'></td><td class='top-right'></td></tr>" +
                "<tr><td class='left'></td><td class='boxy-inner'></td><td class='right'></td></tr>" +
                "<tr><td class='bottom-left'></td><td class='bottom'></td><td class='bottom-right'></td></tr>" +
                "</table>",

    DEFAULTS: {
        title: null,           // titlebar text. titlebar will not be visible if not set.
        closeable: true,           // display close link in titlebar?
        draggable: true,           // can this dialog be dragged?
        clone: false,          // clone content prior to insertion into dialog?
        actuator: null,           // element which opened this dialog
        center: true,           // center dialog in viewport?
        show: true,           // show dialog immediately?
        modal: false,          // make dialog modal?
        fixed: true,           // use fixed positioning, if supported? absolute positioning used otherwise
        closeText: '[关闭]',      // text to use for default close link
        unloadOnHide: false,          // should this dialog be removed from the DOM after being hidden?
        clickToFront: false,          // bring dialog to foreground on any click (not just titlebar)?
        behaviours: Boxy.EF,        // function used to apply behaviours to all content embedded in dialog.
        afterDrop: Boxy.EF,        // callback fired after dialog is dropped. executes in context of Boxy instance.
        afterShow: Boxy.EF,        // callback fired after dialog becomes visible. executes in context of Boxy instance.
        afterHide: Boxy.EF,        // callback fired after dialog is hidden. executed in context of Boxy instance.
        beforeUnload: Boxy.EF         // callback fired after dialog is unloaded. executed in context of Boxy instance.
    },

    DEFAULT_X: 150,
    DEFAULT_Y: 150,
    zIndex: 1337,
    dragConfigured: true, // only set up one drag handler for all boxys
    resizeConfigured: true,
    dragging: null,

    // load a URL and display in boxy
    // url - url to load
    // options keys (any not listed below are passed to boxy constructor)
    //   type: HTTP method, default: GET
    //   cache: cache retrieved content? default: false
    //   filter: jQuery selector used to filter remote content
    load: function (url, options) {

        options = options || {};

        var ajax = {
            url: url, type: 'GET', dataType: 'html', cache: false, success: function (html) {
                html = jQuery(html);
                if (options.filter) html = jQuery(options.filter, html);
                new Boxy(html, options);
            }
        };

        jQuery.each(['type', 'cache'], function () {
            if (this in options) {
                ajax[this] = options[this];
                delete options[this];
            }
        });

        jQuery.ajax(ajax);

    },

    // allows you to get a handle to the containing boxy instance of any element
    // e.g. <a href='#' onclick='alert(Boxy.get(this));'>inspect!</a>.
    // this returns the actual instance of the boxy 'class', not just a DOM element.
    // Boxy.get(this).hide() would be valid, for instance.
    get: function (ele) {
        var p = jQuery(ele).parents('.boxy-wrapper');
        return p.length ? jQuery.data(p[0], 'boxy') : null;
    },

    // returns the boxy instance which has been linked to a given element via the
    // 'actuator' constructor option.
    linkedTo: function (ele) {
        return jQuery.data(ele, 'active.boxy');
    },

    // displays an alert box with a given message, calling optional callback
    // after dismissal.
    alert: function (message, callback, options) {
        return Boxy.ask(message, ['确认'], callback, options);
    },

    // displays an alert box with a given message, calling after callback iff
    // user selects OK.
    confirm: function (message, after, options) {
        return Boxy.ask(message, ['确认', '取消'], function (response) {
            if (response == '确认') after();
        }, options);
    },

    // asks a question with multiple responses presented as buttons
    // selected item is returned to a callback method.
    // answers may be either an array or a hash. if it's an array, the
    // the callback will received the selected value. if it's a hash,
    // you'll get the corresponding key.
    ask: function (question, answers, callback, options) {

        options = jQuery.extend({ modal: false, closeable: false },
                                options || {},
                                { show: true, unloadOnHide: true });

        var body = jQuery('<div></div>').append(jQuery('<div class="question"></div>').html(question));

        // ick
        var map = {}, answerStrings = [];
        if (answers instanceof Array) {
            for (var i = 0; i < answers.length; i++) {
                map[answers[i]] = answers[i];
                answerStrings.push(answers[i]);
            }
        } else {
            for (var k in answers) {
                map[answers[k]] = k;
                answerStrings.push(answers[k]);
            }
        }

        var buttons = jQuery('<form class="answers"></form>');
        buttons.html(jQuery.map(answerStrings, function (v) {
            //add by zhangxinxu http://www.zhangxinxu.com 给确认对话框的确认取消按钮添加不同的class
            var btn_index;
            if (v === "确认") {
                btn_index = 1;
            } else if (v === "取消") {
                btn_index = 2;
            } else {
                btn_index = 3;
            }
            //add end.  include the 'btn_index' below 
            return "<input class='boxy-btn" + btn_index + "' type='button' value='" + v + "' />";
        }).join(' '));

        jQuery('input[type=button]', buttons).click(function () {
            var clicked = this;
            Boxy.get(this).hide(function () {
                if (callback) callback(map[clicked.value]);
            });
        });

        body.append(buttons);

        new Boxy(body, options);

    },

    // 职位类型选择器
    // value 表示选定的职位类型编号，字符创类型，编号间以逗号分隔
    // shown 需要展示项的编号
    // callback 表示回调
    // option 为json格式的可选项的集合
    job: function (value, shown, maxSelectedCount, callback, options) {

        options = jQuery.extend({ modal: false, closeable: true, fixed: false },
                                options || {},
                                { show: true, unloadOnHide: true });

        //最大选择项数
        //var maxSelectedCount = 5;
        //选择项父项和子项的集合
        var arrJobSelectedParents = new Array();
        var arrJobSelectedChildren = new Array();
        //当前展示职位大类    
        var onShownParent = 7;
		
        var job = jQuery("<div></div>").append('<div id="job-main"></div>').css("padding-bottom", "0");

        //获取工作地区主体句柄
        var main = jQuery("#job-main", job);

        var head = jQuery("<div id='job-head' style='margin-bottom:5px;display:none;'></div>").html("<div class='head-title' style='font-weight:bold;height:16px;line-height:16px;'>你的选择结果</div><ul id='job-result'></ul>");

        var body = jQuery("<div id='job-body'></div>");

        var foot = jQuery("<div id='job-foot'><span id='job-msg'>注：限选"+maxSelectedCount+"款已安装游戏作为展示，所以请先 ‘选择安装’</span><span id='job-cancel' >取消</span><span id='job-submit'>确定</span></div>");

        body.append(GetParentItems());

        main.append(head).append(body).append(foot);

        //获取职位大类集合
        function GetParentItems() {
            var items = "";
            for (var i = 0; i < arrclass.length; i++) {
                items += ParentOption(arrclass[i].classId, arrclass[i].className);
            };
            return items;
        };

        //获取每个父项的Html格式
        function ParentOption(id, name) {
            var value = "<a id='parItem" + id + "' href='javascript:void(0);' class='parentItem'><input type='checkbox' /><span type=\"checkbox\" id='parTd" + id + "' style='padding-left:2px;'>" + name + "</span></a>";
            return value;
        };

        //初始化选定职位
        function InitSelData() {
            //设置默认显示职位大类
            var showId = shown + ",";
            if (showId != ",") {
                onShownParent = parseInt(shown);
            }

            var items = (value + ",").split(",");

            arrJobSelectedParents = new Array();
            arrJobSelectedChildren = new Array();

            for (var i = 0; i < items.length - 1; i++) {
                var type = items[i].substr(0, 1);
                var id = items[i].substr(1);
                if (type == "b") {
                    for (var j = 0; j < arrclass.length; j++) {
                        if (parseInt(id) == arrclass[j].classId) {
                            //添加父类项
                            arrJobSelectedParents.push(arrclass[j]);
                        }
                    }
                } else if (type == "s") {
                    for (var j = 0; j < arrjob.length; j++) {
                        if (id == arrjob[j].itemId) {
                            //添加子类项
                            arrJobSelectedChildren.push(arrjob[j]);
                        }
                    }
                }
            }
        };


        //更新被选择项
        function UpdateJobSelItems(isInit) {

            var element = jQuery("#job-result", head);
            //清空DOM中的选择结果
            element.empty();
            //若不存在选择结果，则隐藏选择结果部分
            if (arrJobSelectedParents.length + arrJobSelectedChildren.length == 0) {
                head.slideUp(400);
            }
            else {
                if (isInit) {
                    head.show();
                }
                else {
                    head.slideDown(400);
                }
                var items = "";
                //循环添加父类和子类被选择项
                for (var i = 0; i < arrJobSelectedParents.length; i++) {
                    items += "<li class='jobResultItem'><div class='result-pad-left'></div><span id='parSel" + arrJobSelectedParents[i].classId + "' >" + arrJobSelectedParents[i].className + "</span></li>";
                }
                for (var i = 0; i < arrJobSelectedChildren.length; i++) {
                    items += "<li class='jobResultItem'><div class='result-pad-left'></div><span id='chlSel" + arrJobSelectedChildren[i].itemId + "' >" + arrJobSelectedChildren[i].itemName + "</span></li>";
                }

                if (items != "") {
                    element.append(items);

                    //为新添加项注册事件
                    jQuery(".jobResultItem", element).click(function (event) {
                        var ele = jQuery(event.target);

                        if (ele.attr("class") && ele.attr("class") == "jobResultItem") {
                            DelJobSelItem(event);
                            UpdateJobSelItems();
                            UpdateJobCheckedStatus();
                        }
                    });

                    var resultEle = jQuery("li", element);
                    resultEle.hover(function (event) {
                        var targetEle = jQuery(event.currentTarget);
                        targetEle.css("background-image", "url(source/plugin/bbsgame/images/boxy/del_hover_right.gif)");
                        jQuery("div", targetEle).css("background-image", "url(source/plugin/bbsgame/images/boxy/del_hover_left.gif)");
                        jQuery("span", targetEle).css("background-image", "url(source/plugin/bbsgame/images/boxy/del_hover_center.gif)");
                    }, function (event) {
                        var targetEle = jQuery(event.currentTarget);
                        targetEle.css("background-image", "url(source/plugin/bbsgame/images/boxy/del_normal_right.gif)");
                        jQuery("div", targetEle).css("background-image", "url(source/plugin/bbsgame/images/boxy/del_normal_left.gif)");
                        jQuery("span", targetEle).css("background-image", "url(source/plugin/bbsgame/images/boxy/del_normal_center.gif)");
                    });
                }
            }
        };

        //更新Checked状态
        function UpdateJobCheckedStatus() {
            //清空所有checkbox的checked状态
            //jQuery("input:checkbox", body).attr({ "checked": "" });
			jQuery("input:checkbox", body).removeAttr("checked");
            //清空所有checkbox的checkstyle样式
            jQuery("a", body).removeClass("checkedStyle").removeClass("show-children");

            //循环选定的职位大类集合，为其设置checked状态，及背景样式
            for (var i = 0; i < arrJobSelectedParents.length; i++) {
                var element = jQuery("#parItem" + arrJobSelectedParents[i].classId, body);
                //设置选中项的背景样式
                element.addClass("checkedStyle");
                //根据选中的行业设置checkbox状态
                jQuery(":checkbox", element).attr("checked", "checked");
                if (arrJobSelectedParents[i].classId == onShownParent) {
                    jQuery("#parId" + arrJobSelectedParents[i].classId + " :checkbox", body).attr({ "checked": "checked", "disabled": "disabled" });
                }
            }
			
			for ( var i = 0 ; i < arrJobSelectedChildren.length ;i++) {
					jQuery("#childItem" + arrJobSelectedChildren[i].itemId + " :checkbox", body).attr({ "checked": "checked"});
			}


        };

        //从选择结果集合中移除指定项
        function DelJobSelItem(event) {

            var ele = jQuery(event.currentTarget);

            var jobData = jQuery("span", ele).attr("id").split("Sel");
            var Items = new Array();

            if (jobData[0] == "par") {
                for (var i = 0; i < arrJobSelectedParents.length; i++) {
                    if (arrJobSelectedParents[i].classId != jobData[1]) {
                        Items.push(arrJobSelectedParents[i]);
                    }
                }
                arrJobSelectedParents = Items;
            } else {
                for (var i = 0; i < arrJobSelectedChildren.length; i++) {
                    if (arrJobSelectedChildren[i].itemId != jobData[1]) {
                        Items.push(arrJobSelectedChildren[i]);
                    }
                }
                arrJobSelectedChildren = Items;
            }
        };

        //点击选择或取消行业类别
        jQuery("a.parentItem", body).click(function (event) {

            var id = jQuery(event.currentTarget).attr("id").split("Item")[1];
            var chkEle = jQuery(event.target);
		//	var chkEle = jQuery(event.currentTarget).children(":checkbox");	
				
            if (id != onShownParent) {
                //设置当前展示大类
                onShownParent = id;
                //设置城市下属区域集合所在层
                SetChildrenLayer();
                //设置城市下属区域集合
                SetChildItems();
            }

            if (chkEle.attr("type") && chkEle.attr("type") == "checkbox") {
                var isContain = false;
                var parent = new Array();
                //更新选择结果集合，存在则移除，否则添加
                for (var i = 0; i < arrJobSelectedParents.length; i++) {
                    if (arrJobSelectedParents[i].classId != id) {
                        parent.push(arrJobSelectedParents[i]);
                    } else { isContain = true; }
                }
                if (isContain) {
                    //移除已选择项
                    arrJobSelectedParents = parent;
                } else {

                    var children = new Array();
                    //若子类集合中存在父类项的子集，则将其移除
                    for (var i = 0; i < arrJobSelectedChildren.length; i++) {
                        if (arrJobSelectedChildren[i].classId != id) {
                            children.push(arrJobSelectedChildren[i]);
                        }
                    }
                    //更新子类集合
                    arrJobSelectedChildren = children;

                    if (arrJobSelectedParents.length + arrJobSelectedChildren.length < maxSelectedCount) {
                        //添加新的选择项
                        arrJobSelectedParents.push(new Class(id, jQuery("#parTd" + id, body).text()));
                    } else {
                        Boxy.alert("最多只能选择"+maxSelectedCount+"项，若需要更换其它选项，请先点击取消部分选择结果。", null, { title: "提示信息" });
                    }
                }
                //更新选择结果集合
                UpdateJobSelItems();
            }
            //更新被选项状态
            UpdateJobCheckedStatus();
        }).css("min-width", 187);

        //设置城市下属区域集合所在层
        function SetChildrenLayer() {

            var shouldCreate = false;
            //选定城市下属区域所在层
            var childrenLayer = jQuery(".childrenLayer", body);
            //获取事件触发元素及其外部高度
            var curEle = jQuery("#parItem" + onShownParent, body);
            var eleHeight = curEle.outerHeight(true);
            var childrenlayerHeight = 0;

            if (childrenLayer.attr("class") != null) {
                //获取子层外部高度
                childrenlayerHeight = childrenLayer.outerHeight(true);
                //获取相对父元素的上下偏移
                var layerRelOffsetTop = childrenLayer.offset().top - childrenLayer.parent().offset().top;
                //判断职位小类所在层是否在期望区域(点选的职位大类项的下一行)
                if ((curEle.offset().top < childrenLayer.offset().top) && (childrenLayer.offset().top < (curEle.offset().top + eleHeight * 2))) {
                    //点击同一行但不同职位大类时，清空下属区域层中的所有内容
                    if (childrenLayer.attr("id").split("Id")[1] != onShownParent) {
                        childrenLayer.empty();
                    }
                } else {
                    childrenLayer.remove();
                    //清除样式，防止位置取值时发生错误
                    jQuery("a", body).removeClass("show-children");
                    shouldCreate = true;
                }
            } else {
                shouldCreate = true;
            }
            //创建并插入层
            if (shouldCreate) {
                //重新获取事件触发元素及其外部高度（防止子层被移除发生位置错误）
                curEle = jQuery("#parItem" + onShownParent, body);
                //职位大类集合层内部宽度
                var contentWidth = body.innerWidth();
                //职位大类项的外部宽度
                var itemWidth = jQuery("a.parentItem:first", body).outerWidth(true);
                //获取期望职位大类集合净高度(除去职位小类)
				var eleRelOffsetTop = 0;
				if(curEle.length != 0) eleRelOffsetTop = curEle.offset().top - curEle.parent().offset().top;
                //设定职位小类层插入的垂直位置
                var times = parseInt(eleRelOffsetTop / eleHeight) + 1;
                //获取待插入层的前一个同辈元素序号
                var index = parseInt(contentWidth / itemWidth) * times - 1;

                var layerContent = "<div class='childrenLayer' ></div>";


                var count = jQuery("a.parentItem", body).length;
                if (count <= index + 1) {
                    jQuery("a.parentItem:last", body).after(layerContent);
                } else {
                    //在指定元素后插入层
                    jQuery("a.parentItem:eq(" + index + ")", body).after(layerContent);
                }
                //jQuery(".childrenLayer", body).slideDown(400);
            }
        };

        //设置城市下属区域集合
        function SetChildItems() {

            //职位小类层
            var childrenLayer = jq(".childrenLayer", body);

            if ((onShownParent != "") && (childrenLayer.attr("id") != "parId" + onShownParent)) {
                childrenLayer.attr("id", "parId" + onShownParent);
                for (var i = 0; i < arrjob.length; i++) {
                    if (arrjob[i].classId == onShownParent) {
                        childrenLayer.append(childOption(arrjob[i].itemId, arrjob[i].itemName));
                    }
                }

                jQuery("a", childrenLayer).click(function (event) {
                    childrenClickListener(event);
                });
            }
        };

        //获取城市下属每个区域项的Html格式
        function childOption(id, name) {
            var value = "<a id='childItem" + id + "' href='javascript:void(0);' class='childItem'><input type='checkbox'/><span style='padding-left: 5px;' id='childTd" + id + "'>" + name + "</span></a>";
            return value;
        };

        //工作小类点击事件触发
        function childrenClickListener(event) {

            //获取父项ID
            var parentId = jQuery(event.currentTarget).parent().attr("id").split("Id")[1];
            var isParContained = false;
            //判断父项是否已经被选择
            for (var j = 0; j < arrJobSelectedParents.length; j++) {
                if (arrJobSelectedParents[j].classId == parentId) {
                    isParContained = true;
                    break;
                }
            }

            if (!isParContained) {
                var id = jQuery(event.currentTarget).attr("id").split("Item")[1];
                var isContain = false;
                var children = new Array();
                //更新选择结果集合，存在则移除，否则添加
                for (var i = 0; i < arrJobSelectedChildren.length; i++) {
                    if (arrJobSelectedChildren[i].itemId != id) {
                        children.push(arrJobSelectedChildren[i]);
                    } else { isContain = true; }
                }
                if (isContain) {
                    //移除已选择项
                    arrJobSelectedChildren = children;
                } else {
                    if (arrJobSelectedParents.length + arrJobSelectedChildren.length < maxSelectedCount) {
                        //添加新的选择项
                        arrJobSelectedChildren.push(new Item(id, jQuery("#childTd" + id, body).text(), onShownParent));
                    } else {
                        Boxy.alert("限选"+maxSelectedCount+"项，若需要更换其它选项，请先点击取消部分选择结果。", null, { title: "提示信息" });
                    }
                }
                //更新选择结果集合
                UpdateJobSelItems();
                //更新被选项状态
                UpdateJobCheckedStatus();
            }
        };

        //提交事件
        jQuery("#job-submit", foot).click(function () {

            var value = 0;
            //获取职位大类集合字符串
            for (var i = 0; i < arrJobSelectedParents.length; i++) {
                if (value == 0) {
                    value = "b" + arrJobSelectedParents[i].classId;
                } else {
                    value += ",b" + arrJobSelectedParents[i].classId;
                }
            }
            //获取职位小类集合字符串
            for (var i = 0; i < arrJobSelectedChildren.length; i++) {
                if (value == 0) {
                    value = "s" + arrJobSelectedChildren[i].itemId;
                } else {
                    value += ",s" + arrJobSelectedChildren[i].itemId;
                }
            }
            //返回选择结果
            var clicked = this;
            Boxy.get(this).hide(function () {
                if (callback) callback(value);
            });
        });

        //关闭选择器
        jQuery("#job-cancel", foot).click(function (event) {

            Boxy.get(this).hide();
        });

        //初始化职位小类层
        function InitChildrenLayer() {
            if (onShownParent != "") {
                //设置城市下属区域集合所在层
                SetChildrenLayer();
                //设置城市下属区域集合
                SetChildItems();
            }
        };

        //初始化职位选择器状态
        function InitJobSelectorStatus() {
            var isInit = true;
            //更新选择结果集合
            UpdateJobSelItems(isInit);
            //更新被选项状态
            UpdateJobCheckedStatus();
        };

        //设置工作地区选择器样式
        function SetJobStyle() {
            var title_bar = jQuery(".title-bar", job.parent());
            title_bar.css({ "background-color": "#1E90FF", "padding": 8 });
            jQuery(".close", title_bar).css({ right: 8, top: 7 });
        };

        //        //css设置hover样式在IE没什么效果，所以用程序设置
        //        var linkStyle = jQuery("a", body);
        //        linkStyle.hover(function (event) {
        //            jQuery(event.currentTarget).addClass("hoverStyle");
        //        }, function (event) {
        //            jQuery(event.currentTarget).removeClass("hoverStyle");
        //        });

        new Boxy(job, options);

        //设置工作地区选择器样式
        SetJobStyle();
        //初始化选定职位
        InitSelData();
        //初始化职位小类层
        InitChildrenLayer();
        //初始化职位选择器状态
        InitJobSelectorStatus();

    },

    // returns true if a modal boxy is visible, false otherwise
    isModalVisible: function () {
        return jQuery('.boxy-modal-blackout').length > 0;
    },

    _u: function () {
        for (var i = 0; i < arguments.length; i++)
            if (typeof arguments[i] != 'undefined') return false;
        return true;
    },

    _handleResize: function (evt) {
        var d = jQuery(document);
        jQuery('.boxy-modal-blackout').css('display', 'none').css({
            width: d.width(), height: d.height()
        }).css('display', 'block');
    },

    _handleDrag: function (evt) {
        var d;
        if (d = Boxy.dragging) {
            d[0].boxy.css({ left: evt.pageX - d[1], top: evt.pageY - d[2] });
        }
    },

    _nextZ: function () {
        return Boxy.zIndex++;
    },

    _viewport: function () {
        var d = document.documentElement, b = document.body, w = window;
        return jQuery.extend(
            jQuery.browser.msie ?
                { left: b.scrollLeft || d.scrollLeft, top: b.scrollTop || d.scrollTop} :
                { left: w.pageXOffset, top: w.pageYOffset },
            !Boxy._u(w.innerWidth) ?
                { width: w.innerWidth, height: w.innerHeight} :
                (!Boxy._u(d) && !Boxy._u(d.clientWidth) && d.clientWidth != 0 ?
                    { width: d.clientWidth, height: d.clientHeight} :
                    { width: b.clientWidth, height: b.clientHeight }));
    }

});

Boxy.prototype = {
    
    // Returns the size of this boxy instance without displaying it.
    // Do not use this method if boxy is already visible, use getSize() instead.
    estimateSize: function() {
        this.boxy.css({visibility: 'hidden', display: 'block'});
        var dims = this.getSize();
        this.boxy.css('display', 'none').css('visibility', 'visible');
        return dims;
    },
                
    // Returns the dimensions of the entire boxy dialog as [width,height]
    getSize: function() {
        return [this.boxy.width(), this.boxy.height()];
    },
    
    // Returns the dimensions of the content region as [width,height]
    getContentSize: function() {
        var c = this.getContent();
        return [c.width(), c.height()];
    },
    
    // Returns the position of this dialog as [x,y]
    getPosition: function() {
        var b = this.boxy[0];
        return [b.offsetLeft, b.offsetTop];
    },
    
    // Returns the center point of this dialog as [x,y]
    getCenter: function() {
        var p = this.getPosition();
        var s = this.getSize();
        return [Math.floor(p[0] + s[0] / 2), Math.floor(p[1] + s[1] / 2)];
    },
                
    // Returns a jQuery object wrapping the inner boxy region.
    // Not much reason to use this, you're probably more interested in getContent()
    getInner: function() {
        return jQuery('.boxy-inner', this.boxy);
    },
    
    // Returns a jQuery object wrapping the boxy content region.
    // This is the user-editable content area (i.e. excludes titlebar)
    getContent: function() {
        return jQuery('.boxy-content', this.boxy);
    },
    
    // Replace dialog content
    setContent: function(newContent) {
        newContent = jQuery(newContent).css({display: 'block'}).addClass('boxy-content');
        if (this.options.clone) newContent = newContent.clone(true);
        this.getContent().remove();
        this.getInner().append(newContent);
        this._setupDefaultBehaviours(newContent);
        this.options.behaviours.call(this, newContent);
        return this;
    },
    
    // Move this dialog to some position, funnily enough
    moveTo: function(x, y) {
        this.moveToX(x).moveToY(y);
        return this;
    },
    
    // Move this dialog (x-coord only)
    moveToX: function(x) {
        if (typeof x == 'number') this.boxy.css({left: x});
        else this.centerX();
        return this;
    },
    
    // Move this dialog (y-coord only)
    moveToY: function(y) {
        if (typeof y == 'number') this.boxy.css({top: y});
        else this.centerY();
        return this;
    },
    
    // Move this dialog so that it is centered at (x,y)
    centerAt: function(x, y) {
        var s = this[this.visible ? 'getSize' : 'estimateSize']();
        if (typeof x == 'number') this.moveToX(x - s[0] / 2);
        if (typeof y == 'number') this.moveToY(y - s[1] / 2);
        return this;
    },
    
    centerAtX: function(x) {
        return this.centerAt(x, null);
    },
    
    centerAtY: function(y) {
        return this.centerAt(null, y);
    },
    
    // Center this dialog in the viewport
    // axis is optional, can be 'x', 'y'.
    center: function(axis) {
        var v = Boxy._viewport();
        var o = this.options.fixed ? [0, 0] : [v.left, v.top];
        if (!axis || axis == 'x') this.centerAt(o[0] + v.width / 2, null);
        if (!axis || axis == 'y') this.centerAt(null, o[1] + v.height / 2);
        return this;
    },
    
    // Center this dialog in the viewport (x-coord only)
    centerX: function() {
        return this.center('x');
    },
    
    // Center this dialog in the viewport (y-coord only)
    centerY: function() {
        return this.center('y');
    },
    
    // Resize the content region to a specific size
    resize: function(width, height, after) {
        if (!this.visible) return;
        var bounds = this._getBoundsForResize(width, height);
        this.boxy.css({left: bounds[0], top: bounds[1]});
        this.getContent().css({width: bounds[2], height: bounds[3]});
        if (after) after(this);
        return this;
    },
    
    // Tween the content region to a specific size
    tween: function(width, height, after) {
        if (!this.visible) return;
        var bounds = this._getBoundsForResize(width, height);
        var self = this;
        this.boxy.stop().animate({left: bounds[0], top: bounds[1]});
        this.getContent().stop().animate({width: bounds[2], height: bounds[3]}, function() {
            if (after) after(self);
        });
        return this;
    },
    
    // Returns true if this dialog is visible, false otherwise
    isVisible: function() {
        return this.visible;    
    },
    
    // Make this boxy instance visible
    show: function() {
        if (this.visible) return;
        if (this.options.modal) {
            var self = this;
            if (!Boxy.resizeConfigured) {
                Boxy.resizeConfigured = true;
                jQuery(window).resize(function() { Boxy._handleResize(); });
            }
            this.modalBlackout = jQuery('<div class="boxy-modal-blackout"></div>')
                .css({zIndex: Boxy._nextZ(),
                      opacity: 0.5,
                      width: jQuery(document).width(),
                      height: jQuery(document).height()})
                .appendTo(document.body);
            this.toTop();
            if (this.options.closeable) {
                jQuery(document.body).bind('keypress.boxy', function(evt) {
                    var key = evt.which || evt.keyCode;
                    if (key == 27) {
                        self.hide();
                        jQuery(document.body).unbind('keypress.boxy');
                    }
                });
            }
        }
        this.boxy.stop().css({opacity: 1}).show();
        this.visible = true;
        this._fire('afterShow');
        return this;
    },
    
    // Hide this boxy instance
    hide: function(after) {
        if (!this.visible) return;
        var self = this;
        if (this.options.modal) {
            jQuery(document.body).unbind('keypress.boxy');
            this.modalBlackout.animate({opacity: 0}, function() {
                jQuery(this).remove();
            });
        }
        this.boxy.stop().animate({opacity: 0}, 300, function() {
            self.boxy.css({display: 'none'});
            self.visible = false;
            self._fire('afterHide');
            if (after) after(self);
            if (self.options.unloadOnHide) self.unload();
        });
        return this;
    },
    
    toggle: function() {
        this[this.visible ? 'hide' : 'show']();
        return this;
    },
    
    hideAndUnload: function(after) {
        this.options.unloadOnHide = true;
        this.hide(after);
        return this;
    },
    
    unload: function() {
        this._fire('beforeUnload');
        this.boxy.remove();
        if (this.options.actuator) {
            jQuery.data(this.options.actuator, 'active.boxy', false);
        }
    },
    
    // Move this dialog box above all other boxy instances
    toTop: function() {
        this.boxy.css({zIndex: Boxy._nextZ()});
        return this;
    },
    
    // Returns the title of this dialog
    getTitle: function() {
        return jQuery('> .title-bar h2', this.getInner()).html();
    },
    
    // Sets the title of this dialog
    setTitle: function(t) {
        jQuery('> .title-bar h2', this.getInner()).html(t);
        return this;
    },
    
    //
    // Don't touch these privates
    
    _getBoundsForResize: function(width, height) {
        var csize = this.getContentSize();
        var delta = [width - csize[0], height - csize[1]];
        var p = this.getPosition();
        return [Math.max(p[0] - delta[0] / 2, 0),
                Math.max(p[1] - delta[1] / 2, 0), width, height];
    },
    
    _setupTitleBar: function() {
        if (this.options.title) {
            var self = this;
            var tb = jQuery("<div class='title-bar'></div>").html("<h2>" + this.options.title + "</h2>");
            if (this.options.closeable) {
                tb.append(jQuery("<a href='#' class='close'></a>").html(this.options.closeText));
            }
            if (this.options.draggable) {
                tb[0].onselectstart = function() { return false; }
                tb[0].unselectable = 'on';
                tb[0].style.MozUserSelect = 'none';
                if (!Boxy.dragConfigured) {
                    jQuery(document).mousemove(Boxy._handleDrag);
                    Boxy.dragConfigured = true;
                }
                tb.mousedown(function(evt) {
                    self.toTop();
                    Boxy.dragging = [self, evt.pageX - self.boxy[0].offsetLeft, evt.pageY - self.boxy[0].offsetTop];
                    jQuery(this).addClass('dragging');
                }).mouseup(function() {
                    jQuery(this).removeClass('dragging');
                    Boxy.dragging = null;
                    self._fire('afterDrop');
                });
            }
            this.getInner().prepend(tb);
            this._setupDefaultBehaviours(tb);
        }
    },
    
    _setupDefaultBehaviours: function(root) {
        var self = this;
        if (this.options.clickToFront) {
            root.click(function() { self.toTop(); });
        }
        jQuery('.close', root).click(function() {
            self.hide();
            return false;
        }).mousedown(function(evt) { evt.stopPropagation(); });
    },
    
    _fire: function(event) {
        this.options[event].call(this);
    }
    
};
