var s=true;
function sbtn(){
	$(document).on('mouseenter mouseup','.sbutton,.smbutton',function(){
		$(this).removeClass('active active-o').addClass('hover');})
	.on('mouseleave','.sbutton,.smbutton',function(){
		$(this).removeClass('hover');})
	.on('mousedown','.sbutton,.smbutton',function(){
		$(this).removeClass('hover').addClass('active');})
	.on('disable','.sbutton,.smbutton',function(){
		$(this).addClass('disable').attr('disabled','disabled');})
	.on('enable','.sbutton,.smbutton',function(){
		$(this).removeClass('disable');});
}

$.expr[":"].contains = $.expr.createPseudo(function(arg) {
    return function( elem ) {
        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});
$(document).ready(function(){
	//cookies policy
	if(getCookie('ucp')!=1){
		$('#cookie_policy').addClass('visible');
	}
	$('#cookie_policy button').on('click',function(){
		setCookie('ucp',1,{expires:7776000,path:'/',domain:'.formetoo.ru',secure:true});
		$('#cookie_policy').removeClass('visible');
		return false;
	});

	var wh = $('body').innerHeight();
	$('.main_container_inner').css('min-height',(wh*1-130)+'px');

	if($('.main_products_filters').length) $('.main_content').addClass('wlb');
	else $('.main_content').removeClass('wlb');

	if(!$('#left li').length)
		$('#left').hide();

	if(!$('#breadcrumbs li').length)
		$('#breadcrumbs').remove();

	//ПЕРЕХОД ПО ССЫЛКЕ КАТЕГОРИИ ПО КЛИКУ В БЛОКЕ КАТЕГОРИИ
	$('.main_products_list_items_item.product_category').on('click',function(e){
		window.location=$(this).find('a').attr('href');
	});
	$('.main_products_list_items_item.product_category').on('mouseup',function(e){
		if(e.which==2) return window.open($(this).find('a').attr('href'),'_blank');
	});
	$('.main_products_list_items_item.product_category a').on('mouseup',function(e){
		if(e.which==2) return false;
	});

	if($('#left ul li').length){
		$('#content').css('width','950px');
	}
	else{
		$('#content').css('width','1182px');
	}

	$('#city-switcher a:first,#arrow-switcher').on('click',function(){
		$('#cities,#blocked').show();
		$(this).addClass('hover');
		$('#arrow-switcher').text('▲').css('color','#74AEFF');
		return false;
	});

	$('ol').each(function(index,el){
		$(el).append('<span class="ol-fade"></span>')
	});

	$("#search input")
		.on("focus",function(){
			$(this).parent().addClass("active");
			})
		.on("blur",function(){
			$(this).parent().removeClass("active");
			});

	$("#menu_id_2000000000 > a").on("click",function(){
		return false;
	});
	/* ВЫДЕЛЕНИЕ ПУНКТОВ МЕНЮ ПРИ НАВЕДЕНИИ */
	$(".nav_wrapper li")
		.on("mouseenter",function(){
			$(this).addClass("hover");
		})
		.on("mouseleave",function(){
			$(this).removeClass("hover");
		});

	$('#footer-info-social span')
		.on('mouseenter',function(){
			$(this).addClass('active');
		})
		.on('mouseleave',function(){
			$(this).removeClass('active');
		});

	/* РАСКРЫТИЕ/ЗАКРЫТИЕ АТРИБУТОВ */
	$('.main_products_filters_name').on('click',function(e){
		e.stopPropagation();
		if($(this).next('.main_products_filters_body').hasClass('open')){
			$(this).children('.main_products_filters_name_expand').removeClass('icon-arrow-up').addClass('icon-arrow-down');
			$(this).next('.main_products_filters_body').removeClass('open').hide();}
		else{
			$(this).children('.main_products_filters_name_expand').addClass('icon-arrow-up').removeClass('icon-arrow-down');
			$(this).next('.main_products_filters_body').addClass('open').show();
		}
	});
	$('.main_products_filters_block').on('mousedown',function(e){
		if(e.target==e.currentTarget)
			$(this).find('.main_products_filters_name:first').trigger('click');
	});


	/* ПОКАЗ И СКРЫТИЕ ПОДСКАЗКИ */
	$(".main_products_filters_name_hint").on("click",function(e){
		$(this).addClass("open").nextAll(".main_products_filters_desc_container").fadeIn(200);
		$("#blocked").show();
		e.stopPropagation();
	});
	$('.main_products_filters_desc_container').on('click',function(e){
		e.stopPropagation();
	});
	$(".main_products_filters_desc_container .icon-close").on("click",function(){
		$(this).parents(".main_products_filters_desc_container:first").fadeOut(100);
	});


	/* ТОЛЬКО ЦИФРЫ, ТОЧКА ВМЕСТО ЗАПЯТОЙ, ОКРУГЛЕНИЕ ДО 3-Х ЗНАКОВ ПОСЛЕ ТОЧКИ В ПОЛЯХ С ЧИСЛОВЫМИ ЗНАЧЕНИЯМИ */
	function normalizeNumb(numb){
		numb+="";
		numb=numb.replace(",",".");
		numb=numb.replace(/[^.0-9]/gim,"");
		numb=(numb*1).toFixed(4);
		return numb*1;
	};


	/* ОЧИСТКА INPUT, ВЫДЕЛЕНИЕ ЗАГОЛОВКА АТРИБУТА ПРИ ВНЕСЕНИИ ЗНАЧЕНИЯ В INPUT, СЛАЙДЕР ЗНАЧЕНИЙ */
	$('.rangeinput').each(function(index,el){
		$(el).find('.rangeinput_slider').slider({
			range:true,
			step:0.001,
			min:$(el).find('.rangeinput_from input').data('min-value')*1,
			max:$(el).find('.rangeinput_to input').data('max-value')*1,
			values:[
				($(el).find('.rangeinput_from input').val()
					?$(el).find('.rangeinput_from input').val()*1
					:$(el).find('.rangeinput_from input').attr('placeholder')*1
				),
				($(el).find('.rangeinput_to input').val()
					?$(el).find('.rangeinput_to input').val()*1
					:$(el).find('.rangeinput_to input').attr('placeholder')*1
				)
			],
			slide:function(event,ui){
				$(el).find('input:first').val(ui.values[0]).triggerHandler('change');
				$(el).find('input:last').val(ui.values[1]).triggerHandler('change');
			},
			change: function(event,ui){
				$(el).parents('.main_products_filters_block:first').addClass('active').find('p.main_products_filters_name').addClass('active');
			}
		});
	});
	/* ЗАПОЛНЕНИЕ ЗНАЧЕНИЙ ТЕКСТОВЫХ ФИЛЬТРОВ (ДИАПАЗОНЫ) */
	$('.main_products_filters_block .rangeinput input').on('change',function(){
		var empty=1,
			value_min=$(this).data('min-value'),
			value_max=$(this).data('max-value'),
			parent=$(this).parents('.rangeinput:first');
		page=1;

		$(this).parents('.rangeinput:first').find('input').each(function(index,el){
			if($(el).val())
				empty=0;
		});
		if(!empty)
			$(this).parents('.main_products_filters_block:first').find('p.main_products_filters_name').addClass('active');
		else{
			$(this).parents('.main_products_filters_block:first').find('p.main_products_filters_name').removeClass('active');
		}

		/* if(!$(this).val()){
			$(this).val($(this).attr('placeholder'));
			$(this).parents('.main_products_filters_block:first').removeClass('active').find('p.main_products_filters_name').removeClass('active');
			return;
		} */
		//если поле пустое - подставляем значение из подсказки
		if(!$(this).val())
			$(this).val($(this).attr('placeholder'));

		//если значение меньше предельного - выставляем предельное значение
		if($(this).val()*1<value_min)
			$(this).val(value_min);
		if($(this).val()*1>value_max)
			$(this).val(value_max);

		//если значение ОТ больше значения ДО, выставляем ОТ=ДО и наоборот
		if($(this).val()!=value_min&&$(this).parent().hasClass('rangeinput_from')&&parent.find('.rangeinput_to input').val()&&$(this).val()*1>parent.find('.rangeinput_to input').val()*1)
			$(this).val(parent.find('.rangeinput_to input').val());
		if($(this).val()!=value_max&&$(this).parent().hasClass('rangeinput_to')&&parent.find('.rangeinput_from input').val()&&$(this).val()*1<parent.find('.rangeinput_from input').val()*1)
			$(this).val(parent.find('.rangeinput_from input').val());

		if($(this).parent().hasClass('rangeinput_from')&&!parent.find('.rangeinput_to input').val())
			parent.find('.rangeinput_to input').next().triggerHandler('click');
		if($(this).parent().hasClass('rangeinput_to')&&!parent.find('.rangeinput_from input').val())
			parent.find('.rangeinput_from input').next().triggerHandler('click');

		$(this).parents('.main_products_filters_block:first').addClass('active').find('p.main_products_filters_name').addClass('active');
		//обновляем позиции ползунков при ручной смене значения в поле
		parent.find('.rangeinput_slider').slider("values",0,parent.find('.rangeinput_from input').val()?parent.find('.rangeinput_from input').val():parent.find('.rangeinput_from input').attr('placeholder'));
		parent.find('.rangeinput_slider').slider("values",1,parent.find('.rangeinput_to input').val()?parent.find('.rangeinput_to input').val():parent.find('.rangeinput_to input').attr('placeholder'));

		//если значения не дефолтные, ставим метку изменения поля для включения в запрос
		if(parent.find('.rangeinput_from input').val()!=parent.find('.rangeinput_from input').attr('placeholder')||parent.find('.rangeinput_to input').val()!=parent.find('.rangeinput_to input').attr('placeholder'))
			parent.find('input').addClass('checked');
		else{
			parent.find('input').removeClass('checked');
			parent.parents('.main_products_filters_block:first').removeClass('active').children('p').removeClass('active');
		}
	});
	$('.rangeinput .icon-close').on('click',function(){
		$(this).prev().val('').triggerHandler('change');
	});

	//ВЫДЕЛЕНИЕ ИЗМЕНЕННЫХ ФИЛЬТРОВ, ЗАГРУЖЕННЫХ ЧЕРЕЗ URL
	$('.main_products_filters_block .rangeinput input').each(function(index,el){
		if($(el).val()&&$(el).val()!=$(el).attr('placeholder')){
			$(el).parents('.main_products_filters_block:first').addClass('active').find('p.main_products_filters_name').addClass('active');
			$(el).parents('.main_products_filters_body:first').addClass('open');}
	});
	$('.main_products_filters_block input[type="checkbox"]:checked,.main_products_filters_block input[type="radio"]:checked').each(function(index,el){
		$(el).parents('.main_products_filters_block:first').addClass('active').find('p.main_products_filters_name').addClass('active');
		$(el).parents('.main_products_filters_body:first').addClass('open');
	});

	/* ВЫДЕЛЕНИЕ ЗАГОЛОВКА АТРИБУТА ПРИ ИЗМЕНЕНИИ CHECKBOX ИЛИ RADIO */
	$('.main_products_filters_block').find('input[type="checkbox"],input[type="radio"]').on("change",function(){
		page=1;
		if($(this).parents('ul:first').find('input:checked').length)
			$(this).parents('.main_products_filters_block:first').addClass('active').find('p.main_products_filters_name').addClass('active');
		else{
			$(this).parents('.main_products_filters_block:first').removeClass('active').find('p.main_products_filters_name').removeClass('active');
		}
	});


	/* СНЯТИЕ ПОМЕТКИ С ВЫБРАННОГО RADIO */
	$('.main_products_filters_block').find('input[type="radio"]').next('label').on('click',function(){
		if($(this).prev().prop('checked'))
			$(this).prev().addClass('checked');
	});
	$('.main_products_filters_block').find('input[type="radio"]').on('click',function(){
		if($(this).hasClass('checked')){
			$(this).prop('checked',false);
			$(this).removeClass('checked');
		}
		$(this).triggerHandler('change');
	});

	/* СТИЛИЗОВАННЫЙ SELECT */
	$(document).on('mouseup','.select_default',function(){
		if($(this).hasClass('open')){
			$(this).removeClass('open');
			$(this).parent().css('z-index',90);
			$('#blocked').hide();
		}
		else{
			$('#blocked').show();
			$(this).parent().css('z-index',1010);
			$(this).addClass('open').find('.select_default_option').slideDown(100,'linear');
		}
	});
	sort=$(".main_products_list_toppanel_sort .select_default_option.selected").length?$(".main_products_list_toppanel_sort .select_default_option.selected").attr('data-value'):$(".main_products_list_toppanel_sort .select_default_option:first").attr('data-value');
	//СЕЛЕКТ СОРТИРОВКИ ТОВАРОВ
	$('.main_products_list_toppanel_sort .select_default_option').on('mouseup',function(e){
		//если выбрали новый пункт
		if(!$(this).hasClass('selected')){
			//$(this).parents('.select_default_container').css('z-index',900);
			sort=$(this).attr('data-value');
			page=1;
			more=0;
			$('.main_products_filters_count_button.button_show').triggerHandler('click');
		}
	});
	//все стилизованные селекты
	$(document).on('mouseup','.select_default_option',function(e){
		var name=$(this).text(),
			width=$(this).parents('.select_default:first').data('max-width');console.log(width);
		if(name.length>Math.floor(width/7))
			cut_name=name.substr(0,Math.floor(width/7))+'…';
		else cut_name=name;
		//$(this).parents('.select_default_container').css('z-index',900);
		$('#blocked').hide();
		$(this).parent().prev().attr('title',name).text(cut_name);
		$(this).parent().children().hide().removeClass('selected');
		$(this).addClass('selected');
		if($(this).parents('.select_default:first').find('input').length)
			$(this).parents('.select_default:first').find('input').val($(this).attr('data-value')).trigger('change');
	});
	$('.select_default_option_selected').each(function(index,el){
		var name=$(this).text(),
			width=$(el).parent().data('max-width');
		if(name.length>Math.floor(width/7))
			cut_name=name.substr(0,Math.floor(width/7))+'…';
		else cut_name=name;
		$(this).attr('title',name).text(cut_name);
	});
	$(document).on('mouseup','.select_default span,.select_default_option_selected',function(e){
		if($(this).parent().hasClass('open')){
			$(this).parent().removeClass('open').find('.select_default_option').hide();
			e.stopPropagation();
			$(this).parent().css('z-index',90);
		}
	});


	/* ПОИСК */
	$('#search input').sug();
	$('#search input').on('keyup keypress enter',function(e){
		if(e.which==13)
			return false;
	});

	/* ФИЛЬТРЫ, СОРТИРОВКА, ПАГИНАЦИЯ AJAX */
	$(document).on('click','.pagination-button:not(.good_variants)',function(){
		if(!$(this).hasClass('inactive')){
			page*=1;
			if($(this).hasClass('prev')){
				page-=1;
				more=0;
			}
			else{
				if($(this).hasClass('next')){
					page+=1;
					more=0;
				}
				else{
					if($(this).hasClass('more')){
						page+=1;
						more=1;
					}
					else{
						page=$(this).text();
						more=0;
					}
				}
			}
			if(!$(this).hasClass('more'))
				$('html, body').animate({scrollTop: 0},500);
			$('.main_products_filters_count_button.button_show').triggerHandler('click');
		}
		return false;
	});
	/* КНОПКА "ПОКАЗАТЬ" */
	$('.main_products_filters_count_button.button_show').on('click',function(){
		var q=[],
			u = new Url;
		//формируем запрос со значениями атрибутов
		$('.main_products_filters_body input:checkbox:checked,.main_products_filters_body input:radio:checked,.main_products_filters_body input:text[value!=""]').each(function(index,el){
			//если значение не пустое
			if($(el).val()){
				//если тип атрибуте не текстовый, или текстовый и значение хотя бы одного из полей отличное от дефолтного
				if($(el).attr('type')!='text'||($(el).attr('type')=='text'&&$(el).hasClass('checked')))
					q.push($(el).attr('name')+'='+$(el).val());
			}
		});

		//подставляем url с фильтрами в адресную строку
		q.push('p='+page);
		q.push('sort='+sort);
		q.push('limit='+($('.clwg').length?30:24));
		q=q.join('&');
		u.clearQuery();
		u.query=q;
		try {
			history.pushState(null,null,decodeURIComponent(u));
		}
		catch(e){}

		q+='&current='+current;
		$.get(
			'/ajax/filters.php',
			q,
			function(data){
				//нажата кнопка "показать ещё" в списке товаров
				if(more){
					$('.main_products_list_items_more,.main_products_list_items_pagination_container,.main_products_list_items_more_delimeter').remove();
					$('.main_products_list_items:last').append(data);
				}
				else{
					$('.main_products_list_items:last').html(data);
				}
				/* if(attrs_count!='undefined'){
					$('.main_products_filters input').each(function(index,el){
						if(attrs_count[$(el).attr('name').substr(7,10)]!='undefined'){
							var cnt=0,
								obj=attrs_count[$(el).attr('name').substr(7,10)];
							for(key in obj){
								if($(el).next().text().indexOf(obj[key]['m_products_attributes_id'])!=-1)
									cnt++;
							};
							$(el).next().find('span').text("("+cnt+")");
						}
					});
				} */
				$('.main_products_filters_count p span').text($('.main_products_list_items_item_count').attr('data-count'));
			},
			'html'
		);
	});
	/* КНОПКА СБРОСИТЬ */
	$('.main_products_filters_count_button.button_reset').on('click',function(){
		var u = new Url;
		u.clearQuery();
		window.location=u;
	});
	$(document).on('click','.main_products_list_null a',function(){$('.main_products_filters_count_button.button_reset').triggerHandler('click');});

	/* НАЛИЧИЕ ТОВАРА */
	$(document).on('click','.get_exist',function(){
		var container=$(this).parent(),
			self=$(this);
		container.hide();
		container.prev().show();
		$.get(
			'/ajax/get_exist.php',
			{
				id:self.data('product-id')
			},
			function(data){

				container.prev().hide();
				container.show();
				if(data&&data!=0)
					container.html('<span title="Есть в наличии на складе в Москве">'+data+' '+self.data('product-unit')+'<span>');
				else{
					container.html('<span title="Под заказ, срок поставки по согласованию с менеджером">под заказ<span>').removeClass('exist-1').addClass('exist-0');}
			}
		);
		return false;
	});

	/* ВЫБОР ГОРОДА */
	$(".select_city_search input").on("keyup",function(){
		$(".select_city_list a").hide();
		$(".select_city_list a:contains('"+$(this).val()+"')").show();
	});

	$('#region-yes').on('click',function(){
		setCookie('regionselect',1,{
			path: '/',
			expires: 2592000,
			domain: 'Formetoo.ru'
		});
		$('#blocked').triggerHandler('click');
		$('#region-question').hide();
	});
	$('#region-no').on('click',function(){
		setCookie('regionselect',1,{
			path: '/',
			expires: 2592000,
			domain: 'Formetoo.ru'
		});
		$('#blocked').triggerHandler('click');
		$('#region-question').hide();
		$('#city-switcher a:first').triggerHandler('click');
	})


	$('#city').on('click',function(){
		popup_show('#select_city',function(){$('[name="city_search"]').focus();});
		$(this).addClass('hover');
		return false;
	});

	//перемещение всплывающих элементов в конец страницы
	$('.popup').each(function(index,el){
		$('#popups').append($(el));
	});
	$('#popups,.popup').css({'display':'none','visibility':'visible'});
	//убираем всплывающие блоки при клике вне их
	$('#blocked,.popup_close,.popup_cancel,.icon-close').on('click',function(){
		//скрытие инфоблоков, показанных поверх попапов
		if($('#popup_infobox:visible').length&&$('.popup:visible').length>1){
			$($('#popup_infobox:visible')).fadeOut(200,function(){
					$(this).css('z-index',1010);
					$($('#blocked')).css('z-index',1001);
					$($('#popups > div:not(#popup_infobox)')).css('box-shadow','none');
				});
			return false;
		}
		$('#blocked').removeClass('dark');
		$('.select_default').parent().css('z-index',90);
		$('.select_default').removeClass('open').find('.select_default_option').hide();
		$('.minipopup,.popup,#blocked,#popups,.main_products_filters_desc_container').hide();
		$('a').removeClass('hover');
		$('.main_products_filters_name_hint').removeClass("open");
		$('#arrow-switcher').text('▼').css('color','#FC7E29');
		return false;
	});

	$('.nav_account,.nav_cart,.nav_logout')
		.on('mouseenter',function(){
			$(this).addClass('active');
		})
		.on('mouseleave',function(){
			$(this).removeClass('active');
		});
	/* $('.nav_account')
		.on('mouseenter',function(){
			$('.sublevel_logout').show();
			$("#nav_shadow").fadeIn(100);
		})
		.on('mouseleave',function(){
			$('.sublevel_logout').hide();
			$("#nav_shadow").fadeOut(100);
		}); */
	$('.nav_cart,.nav_account,.nav_logout').on('mouseup',function(e){
		if(e.which==1)
			return window.location=$(this).data('href');
		if(e.which==2)
			return window.open($(this).data('href'),'_blank');
	});

	//КОРЗИНА
	$(document).on('click','.btn-cart',function(){
		var container=$(this).parent(),
			self=$(this);

		$.post(
			'/ajax/add_cart.php',
			{
				product_id:container.find('.product_id').data('value'),
				product_count:container.find('.product_count').data('value')
			},
			function(data){
				var cart_data=null;
				try{
					cart_data=$.parseJSON(data)
				}
				catch(e){
					$('.nav_cart_size').text(data.items.length).removeClass('active');
				}
				if(cart_data!==null){
					$('.nav_cart .desc').html(cart_data.sum.toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;р.');
					$('.nav_cart_size').text(cart_data.items.length).addClass('active');
					self.text('Добавлен в корзину').addClass('success');
					$('.nav_cart').addClass('success');
					setTimeout(function(){
						$('.nav_cart').removeClass('success');
					},100);
					setTimeout(function(){
						self.text('В корзину').removeClass('success');
					},1000);
					cart=cart_data;
				}
			}
		);
	});


	//ПОКАЗАТЬ ОСТАТОК ТЕКСТА
	$('.hidden_text_show').on('click',function(){
		$(this).nextAll().show();
		$(this).remove();
		return false;
	});
	//ТОНКОЕ ПОДЧЁРКИВАНИЕ У ССЫЛОК
	$('a.underline,a.dotted,a.dashed').each(function(index,el){
		var color='rgba'+$(el).css('color').match(/\((.*?)\)/gi);
		color=color.substr(0,color.indexOf(')'))+', .4)';
		$(el).css('border-bottom-color',color);
		$(el)
			.on('mouseenter mouseleave',function(){
				var color='rgba'+$(el).css('color').match(/\((.*?)\)/gi);
				color=color.substr(0,color.indexOf(')'))+', .4)';
				$(el).css('border-bottom-color',color);
			});
	});

	//СКРЫТИЕ ГРАДИЕНТНОГО РАЗМЫТИЯ ПРИ ДОСТИЖЕНИИ НАЧАЛА И КОНЦА ПРОКРУТКИ
	simpleBarGrad=function(el){
		el=el||document;
		$(el).find('.simplebar-scroll-content').on('scroll',function(e){
			if(e.currentTarget.scrollTop==0)
				$(this).addClass('hide-grad-top');
			else ($(this).removeClass('hide-grad-top'));
			if(e.currentTarget.scrollTop+e.currentTarget.clientHeight==e.currentTarget.scrollHeight)
				$(this).addClass('hide-grad-bottom');
			else ($(this).removeClass('hide-grad-bottom'));
		}).addClass('hide-grad-top');
		//скрываем размытие у выбранных элементов
		$(el).find('.simplebar-track.vertical').filter(function(){
			//выбираем только видимые элементы, у которых скрыта прокрутка (высота из-за кол-ва элементов не превышает максимальную)
			return ($(this).is(':visible')&&$(this).css('visibility')=='hidden');
		}).siblings('.simplebar-scroll-content').addClass('hide-grad-top').addClass('hide-grad-bottom');
	}
	simpleBarGrad();

	//СООБЩЕНИЕ НА WHATSAPP
	// $(document).on('click','.whatsapp_href',function() {
	// 	var u = window.location.href;
	// 	location.href=('https://api.whatsapp.com/send?phone=79105199977');
	// });

	//СМЕНА ЦВЕТА ПРИ КЛИКЕ ЗНАЧКОВ НРАВИТСЯ И СРАВНИТЬ
	$('.like').click(function() {
		$(this).toggleClass('active');
	});

	$('.comparison').click(function() {
		$(this).toggleClass('active');
	});

	$('.good_info_comparison_a').click(function() {
		$(this).toggleClass('active');
	});

	$('.good_info_goods_a').click(function() {
		$(this).toggleClass('active');
	});

	//ПОДПИСКА НА РАССЫЛКУ БЕЗ РЕГИСТРАЦИИ
	$.validator.methods.email=function(value,element) {
		return this.optional(element)||/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i.test(value);
	}
	$("#subscribe_form").validate({
		rules:{
			email:{
				email:true,
				required:true
			}
		},
		submitHandler: function(form) {
			$.post(
				'/ajax/add_subscribe.php',
				{
					email:$('#subscribe_form [name="email"]').val(),
					politic:$('#subscribe_form [name="politic"]:checked').val(),
					token:$('#subscribe_form [name="token"]').val()
				},
				function(data){
					console.log(data);
					if(data=='OK')
						//ПОПАП ПОДПИСКИ
						popup_show('#popup_subscribe',function(){
							$('#popup_subscribe p.popup-error').hide();
							$('#popup_subscribe p.popup-success-email-confirmed').hide();
							$('#popup_subscribe p.popup-success').show();
							$('#subscribe_form [name="email"]').val('');
							return true;
						});
					else if(data=='OK_CONFIRM')
						popup_show('#popup_subscribe',function(){
							$('#popup_subscribe p.popup-error').hide();
							$('#popup_subscribe p.popup-success').hide();
							$('#popup_subscribe p.popup-success-email-confirmed"').show();
							$('#subscribe_form [name="email"]').val('');
							return true;
						});
					else popup_show('#popup_subscribe',function(){
						$('#popup_subscribe p.popup-success').hide();
						$('#popup_subscribe p.popup-success-email-confirmed').hide();
						$('#popup_subscribe p.popup-error').show();
						return false;
					});
				}
			);
			$('#subscribe_form button[type=submit]').prop('disabled',true);
			return false;
		}
	});
	$("#subscribe_form button[type=submit]").on('click',function(){
		$('#subscribe_form input').each(function(index,el){
			if($(el).attr('placeholder')&&$(el).attr('placeholder')!='email'&&!$(el).val())
				$(el).val($(el).attr('placeholder'));
		});
	});
	$("#subscribe_form").on('submit',function(){
		if(!$("#subscribe_form").validate())
			return false;
	});
	$('#subscribe_form #politic_subscribe').on('change',function(){
		if(!$(this).prop('checked'))
			$('#subscribe_form button[type=submit]').prop('disabled',true);
		else $('#subscribe_form button[type=submit]').prop('disabled',false);
	});

	//обновление капчи
	$('.captcha_img').on('click',function(){
		$(this).attr('src','/ajax/captcha/captcha.php?'+Math.random(1,10000));
		$(this).parents('form:first').find('[name="captcha"]').val('').focus();
	});

	//verify ssl logo
	setTimeout(function(){
		$('#footer-info-paysystems .ssl-verify').html('<table width=125 border=0 cellspacing=0 cellpadding=0 title="CLICK TO VERIFY: This site uses a GlobalSign SSL Certificate to secure your personal information." ><tr><td><span id="ss_img_wrapper_gmogs_image_90-35_en_white"><a href="https://www.globalsign.com/" target=_blank title="GlobalSign Site Seal" rel="nofollow"><img alt="SSL" border=0 id="ss_img" src="//seal.globalsign.com/SiteSeal/images/gs_noscript_90-35_en.gif"></a></span><script type="text/javascript" src="//seal.globalsign.com/SiteSeal/gmogs_image_90-35_en_white.js"></script></td></tr></table>');
		if($('.main_products_list_items_info_pay_types_icons:eq(2)').find('.ssl-verify').length)
			$('.main_products_list_items_info_pay_types_icons:eq(2)').find('.ssl-verify').html('<table width=125 border=0 cellspacing=0 cellpadding=0 title="CLICK TO VERIFY: This site uses a GlobalSign SSL Certificate to secure your personal information." ><tr><td><span id="ss_img_wrapper_gmogs_image_125-50_en_dblue"><a href="https://www.globalsign.com/" target=_blank title="GlobalSign Site Seal" rel="nofollow"><img alt="SSL" border=0 id="ss_img" src="//seal.globalsign.com/SiteSeal/images/gs_noscript_125-50_en.gif"></a></span><script type="text/javascript" src="//seal.globalsign.com/SiteSeal/gmogs_image_125-50_en_dblue.js"></script></td></tr></table>');
	},500);

	var mqn = window.matchMedia('only screen and (min-width: 769px) and (max-width: 999px)');
	if (mqn.matches) {
		$('.nav_wrapper').click(function () {
			$(this).toggleClass('active_mobile');
			$('body').toggleClass('active_cat');
			$('.city_account').toggleClass('active_city_account');
		});
	};
	var mql = window.matchMedia('only screen and (max-width: 768px)');
	if (mql.matches) {
		$('.nav_wrapper > span').click(function () {
			$(this).toggleClass('active');
			$(this).parent().toggleClass('active_mobile');
			$('body').toggleClass('active_cat');
			$('.city_account').toggleClass('active_city_account');
		});
		$('.nav_wrapper > ul.menu__list > .menu__item > a').removeAttr('href');
		$('.nav_wrapper > ul.menu__list > .menu__item').click(function () {
			$(this).toggleClass('active');
		})
		$('#popup_quicksubmit').css({'width':'50%', 'left':'25%', 'height':'82%', 'box-sizing':'border-box', 'top':'10%', 'margin':'0px'});
	};
	var mql = window.matchMedia('only screen and (max-width: 480px)');
	if (mql.matches) {
		$('#popup_quicksubmit').css({'width':'90%', 'left':'5%', 'height':'82%', 'box-sizing':'border-box', 'top':'10%', 'margin':'0px'});
	};

	$('.main_products_list_items_item').hover(function() {
		$(this).children('.char_parent, .good_icons').toggleClass('active');
		if ($(this).children('.char_parent').length > 0) {
			$(this).toggleClass('active');
			$(this).children('.good_icons').toggleClass('shadow');
		};
	});
});
