/* v.1.1 */
var methods = {
	init:function(o){
		var o = $.extend({
			max:10,
			f_a:function(){},
			f_d:function(){}
		}, o);
		return $(this).each(function(){
			var self=$(this),
				id=self.attr('id'),
				count=self.children('.multirow').length,
				str=self.children('.multirow:last').clone();
				str.find('input:not([data-clean="no"])').val('');
			$(document).on('click','#'+id+' > .multirow > .row > .multirow-btn .add',function(){
				if (count<o.max){
					/* добавление строки после в конец блока */
					//self.append(str.clone()).children('.multirow:last').find('input:first').trigger('focus');
					/* добавление строки после той, на которой сработало событие */
					$(this).parents('.multirow:first').after(str.clone()).next().find('input:first').trigger('focus');
					count++;
					/* передаём в функцию после добавления добавленную строку */
					o.f_a($(this).parents('.multirow:first').next());
				}
				if(count>=o.max)
					return false;
			});
			$(document).on('click','#'+id+' > .multirow > .row > .multirow-btn .copy',function(){
				if (count<o.max){
					var copy_str=$(this).parents('.multirow:first');
					/* добавление строки после той, на которой сработало событие */
					copy_str.after(copy_str.clone()).next().find('input:first').trigger('focus');
					count++;
					/* передаём в функцию после добавления добавленную строку */
					o.f_a(copy_str.next());
				}
				if(count>=o.max)
					return false;
			});
			$(document).on('click','#'+id+' > .multirow > .row > .multirow-btn .delete',function(){
				if(self.children('.multirow').length>1){
					$(this).parents('.multirow:first').remove()
					count--;
					o.f_d();
				}
				return false;
			});
		});
	},
	clear:function(){
		$(this).each(function(){
			var self=$(this),
				count=self.children('.multirow').length;
			for(var i=1;i<count;i++)
				self.children('.multirow').find('.delete').trigger('click');
			self.find('input[type="text"]').val('');
		});
	}
};
jQuery.fn.df = function(method) {
	if(methods[method]) return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
	else if(typeof method==='object'||!method) return methods.init.apply(this,arguments);
	else $.error('Метод с именем'+method+'не существует для jQuery.df');
}