var replace='',
	interval_sug_units=0,
	sb_sug_units;
jQuery.fn.sug_addr = function(o) {
	// параметры по умолчанию
	var o = $.extend({
		// URL для поиска слов
		url:'/ajax/suggest_addr.php',
		//поле для подстановки значения
		idValue:'add_address',
		// функция, которая срабатывает при закрытии окна с подсказками
		onClose:function(suggest) { 
			suggest.hide();
		},
		// функция, возвращающая данные для отправки на сервер
		dataSend:function(input) {  
			return 'addr='+word;
		},
		// функция, которая срабатывает при добавлении слова в input (кнопками вверх-вниз)
		wordClick:function(input,link){
			input.val(link.attr('href')).focus();
			id=link.data('response');
			//полный адрес (разобранный)
			input.parents('div:first').find("input[name='"+o.idValue+"']").val(link.attr('rel'));
			//json ответ сервера
			input.parents('div:first').find("input[name="+o.idValue+"_]").val(JSON.stringify(id)).trigger('change');
			var suggest = input.next();
			suggest.hide();
		}
	}, o);
	// каждое поле для ввода
	return $(this).each(function(){ 
		var onClose = o.onClose;
		// присваиваем переменной input
		var input = $(this); 
		// после него вставляем блок для подсказок
		input.after('<div class="suggest"></div>'); 
		// присваиваем его переменной
		var suggest = input.next();
		// выставляем для него ширину
		suggest.css('width',suggest.prev().width()+suggest.prev().css('padding-right').substring(0,2)*1);
		// когда input не в фокусе
		input.blur(function(){ 
			// если подсказки не скрыты
			if (suggest.is(':visible'))  {  
				// скрываем подсказки
				onClose(suggest); 
			}
		})
		//навигация клавишами
		.keydown(function(e) {
			//ширина выпадающего списка для динамических полей
			
			suggest.css('width',suggest.prev().width()+suggest.prev().css('padding-right').substring(0,2)*1-5);
			//если поле подсказок показано
			if ($('.suggest:visible').length){
				 // если эта клавиша вверх или вниз
				if (e.keyCode == 38 || e.keyCode == 40) {
					// находим выделенный пункт
					var tag = suggest.find('a.suggest-selected'),
					 // и первый в списке
					new_tag = suggest.find('a:first');
					// если выделение существует
					if (tag.length){
						// нажата клавиша вверх
						if (e.keyCode == 38){ 
							// и не выделен первый пункт
							if (suggest.find('a:first').attr('class')!='suggest-selected') 
								// выделяем предыдущий
								new_tag = tag.prev('a');  
							// если выделен первый пункт выделяем последний
							else
								new_tag = suggest.find('a:last');
						//если нажата стрелка вниз
						} else
							//если пункт не последний  выделяем следующий
							if (suggest.find('a:last').attr('class')!='suggest-selected') 
								new_tag = tag.next('a');
							else
								// выделяем первый
								new_tag = suggest.find('a:first');
						// снимаем выделение со старого пункта
						tag.removeClass('suggest-selected');
					}
					// добавляем класс выделения
					new_tag.addClass('suggest-selected');
					// заменяем слово в поле ввода
					input.val(new_tag.attr('rel'));
					id=suggest.find('a.suggest-selected').data('response');
					//полный адрес (разобранный)
					input.parents('div:first').find("input[name="+o.idValue+"]").val(new_tag.attr('rel'));
					//json ответ сервера
					input.parents('div:first').find("input[name="+o.idValue+"_]").val(JSON.stringify(id)).trigger('change');
					//смещение simplebar за выбранныйм элентом
					//if(e.keyCode == 40)
					//	sb_sug_units.scrollContentEl.scrollTop+=new_tag.position().top-new_tag.height();
					//else sb_sug_units.scrollContentEl.scrollTop-=-new_tag.position().top+new_tag.height();
					
					return false;
				}
				 // если нажата клавиша Esc
				if (e.keyCode == 27) {
					// закрываем окно
					onClose(suggest);
					return false;
				}
				if (e.keyCode == 13) {
					// закрываем окно
					if(suggest.find('a.suggest-selected').length)
						o.wordClick(input,suggest.find('a.suggest-selected'));
					return false;
				}
			}
		})
		//поиск
		.keyup(function(e) {
			//сбрасываем таймаут
			if(interval_sug_units)
				clearTimeout(interval_sug_units);
			//устанавливаем таймаут нажатия клавиши в 0,3 секунды
			interval_sug_units=setTimeout(function(){
				// если нажата одна из клавиш, выходим
				if (e.keyCode == 38 || e.keyCode == 40 || e.keyCode == 13 || e.keyCode == 27) return false; 
				if (input.val()=='') replace='';
				 // добавляем переменную со значением поля ввода
				word = input.val()=='  '?'all_units':input.val();
				// если переменная не пуста
				if (word) {
					$.get(
						o.url,
						o.dataSend(input),
						// функция при завершении запроса
						function(data){
							// если есть список подходящих слов
							if (data.length>0&&data!='NULL_RESULT_ERROR'&&data!='INPUT_DATA_ERROR'&&data!='ERROR'&&data!='E_ERROR') {
								// функция, срабатывающая при нажатии на слово
								suggest.html(data).show(0,function(){
										//sb_sug_units=new SimpleBar(suggest.find('.suggest-container')[0],{autoHide:false});
									})
									.find('a').on('mousedown click',function(k){
										// пользовательская функция, объявленная выше
										if(k.which==3||k.which==2) return false;
										o.wordClick(input,$(this));
										return false;
									});
							} else {  
								onClose(suggest);
							}
						}
					);
				// если переменная пуста закрываем окно
				}else{
					onClose(suggest); 
				}
			//задержка при вводе в input, без зажержки показ всех вариантов	
			},300);		
		})
		.click(function(e){
			if(suggest.find('a').length)
				suggest.show();
		})
	});
}