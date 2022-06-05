//启动事件
$(function(){
	//导航栏自动收起来
	(function(){
		var $body = $("body");
		var $top = $(".top-container");
		var $nav = $(".top-nav");
		var ckWidth = $(".top-logo").width() + 5;
		var flag = true;
		getNavWidth();  //获取导航条宽度
		navChange();    //根据宽度判断是否收起
		$(window).resize(navChange);  //当文档宽度发生改变时，判断是否收起
		function navChange(){
			if($top.width() < ckWidth){
				//收起来
				if(flag){
					$nav.hide();
					$body.addClass("jq-toggle-cls");
					flag = false;
				}
			}else if(!flag){
				$nav.show();
				$body.removeClass("jq-toggle-cls");
				flag = true;
			}
		}
		//单击展开按钮
		$(".jq-toggle-btn").click(function(){
			$(".top-nav").slideToggle(200);
		});
		//获取导航条宽度
		function getNavWidth(){
			$nav.find("a").each(function(){
				ckWidth += $(this).outerWidth(true);
			});
		}
	})();
	//幻灯片切换
	(function(){
		var $slideWrap = $(".slide-wrap");     //外层容器
		var $imgWrap = $(".slide-wrap ul");    //图片容器
		var $barLi = $('.slide-circle li');    //小圆点按钮
		var speed = 700;  //动画时间
		var delay = 6000; //自动切换的间隔时间
		var now = 0;      //当前显示的图片索引
		var height = 0;   //图片高度
		var ratio = 24 / 7;        //计算比例 24:7
		var max = $barLi.length-1; //图片的最大索引
		var timer = null;          //计时器
		//根据宽度自动计算高度
		autoResize();
		$(window).resize(autoResize); //当文档宽度改变时自动修改高度值
		function autoResize(){
			height = parseInt($slideWrap.width() / ratio);
			$slideWrap.height(height);
		}
		//复制列表中的第一个图片，追加到列表最后
		$imgWrap.find('li:first').clone().appendTo($imgWrap);
		//设置周期计时器，实现图片自动切换
		timer = setInterval(autoChange,delay);
		//鼠标滑过时暂停移动，移出时恢复移动
		$('.slide').on({
			mouseenter:function(){
				clearInterval(timer);
			},
			mouseleave:function(){
				clearInterval(timer);
				timer = setInterval(autoChange,delay);
			}
		});
		//鼠标滑过小圆点切换图片
		$barLi.mouseenter(function(){
			now = $(this).index(); //当前要显示的索引
			$imgWrap.stop(); //先停止动画
			changeNext(); //切换图片
			changeBar(); //切换小圆点
		});
		//图片自动切换
		function autoChange(){
			if(!$imgWrap.is(':animated')){
				//判断是否达到图片列表末尾
				if(now < max){
					now += 1;
					changeNext();
				}else{
					now = 0;
					changeReset();
				}
				changeBar();
			}
		}
		//切换到下一张图片
		function changeNext(){
			$imgWrap.animate({top:-height*now},speed);
		}
		//切换到第一张图片
		function changeReset(){
			$imgWrap.animate({top:-height*(max+1)},speed,function(){
				$(this).css("top",0);
			});
		}
		//小圆点切换
		function changeBar(){
			$barLi.eq(now).addClass("on").siblings().removeClass("on");
		}
	})();
});