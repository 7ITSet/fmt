/*
 * Translated default messages for the jQuery validation plugin.
 * Locale: RU (Russian; русский язык)
 */
$.extend( $.validator.messages, {
	required: "Обязательно для заполнения",
	remote: "Значение не прошло проверку",
	email: "Введите корректный email",
	url: "Введите корректный URL",
	date: "Введите корректную дату",
	dateISO: "Введите корректную дату в формате ISO",
	number: "Только числа",
	digits: "Только цифры",
	creditcard: "Введите правильный номер кредитной карты",
	equalTo: "Введите такое же значение ещё раз",
	extension: "Выберите файл с правильным расширением",
	maxlength: $.validator.format( "Макс. {0} символов" ),
	minlength: $.validator.format( "Мин. {0} символов" ),
	rangelength: $.validator.format( "Длина от {0} до {1} символов" ),
	range: $.validator.format( "Число от {0} до {1}" ),
	max: $.validator.format( "Число, меньшее или равное {0}" ),
	min: $.validator.format( "Число, большее или равное {0}" ),
	tel: $.validator.format( "Формат: +7 ××× ×××–××–××" ),
	inn: $.validator.format( "Некорректный ИНН" ),
	inn_unique: $.validator.format( "Такой ИНН+КПП уже есть в системе" )
} );
