//******************************************************************************************
// * jQuery Multi Level CSS Menu #2- By Dynamic Drive: http://www.dynamicdrive.com/
// * Last update: Nov 7th, 08': Limit # of queued animations to minmize animation stuttering
// * Menu avaiable at DD CSS Library: http://www.dynamicdrive.com/style/
//******************************************************************************************
// Update: April 12th, 10: Fixed compat issue with jquery 1.4x
// Update: x.09.11 - updated script, deleted images, no need (jquery 1.6.2) by sam3000
//******************************************************************************************

var jqueryslidemenu = {
	animateduration: {over:1, out:1}, //duration of slide in/ out animation, in milliseconds
	buildmenu: function(menuid) {
		$(document).ready(function() {
			var mainmenu = $("#" + menuid + " > ul");
			var headers = mainmenu.find("ul").parent();
			headers.each(function() {
				var curobj = $(this);
				var subul = $(this).find('ul:eq(0)');
				this._dimensions = {w:this.offsetWidth, h:this.offsetHeight, subulw:subul.outerWidth(), subulh:subul.outerHeight()};
				this.istopheader = curobj.parents("ul").length == 1 ? true : false;
				subul.css({top:this.istopheader ? this._dimensions.h+"px" : 0});
				curobj.hover(
					function() {
						$(this).children("a:eq(0)").addClass("hover");
						var targetul = $(this).children("ul:eq(0)");
						this._offsets = {left:0, top:$(this).offset().top};
						var menuleft = this.istopheader ? 0 : this._dimensions.w;
						if ((this._offsets.left + menuleft + this._dimensions.subulw) > $(window).width())
							menuleft = (this.istopheader ? (-this._dimensions.subulw + this._dimensions.w) : -this._dimensions.w);
						if (targetul.queue().length <= 1)
							targetul.css({left:menuleft + "px", width:this._dimensions.w + 'px'}).slideDown(jqueryslidemenu.animateduration.over);
					},
					function() {
						$(this).children("a:eq(0)").removeClass("hover");
						var targetul = $(this).children("ul:eq(0)");
						targetul.slideUp(jqueryslidemenu.animateduration.out);
					}
				)
				curobj.click(function() {
					$(this).children("ul:eq(0)").hide();
				});
			});
			mainmenu.find("ul").css({display:'none', visibility:'visible'})
		});
	}
};

jqueryslidemenu.buildmenu("myslidemenu"); // id