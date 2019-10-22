<?
defined ('_DSITE') or die ('Access denied');
/* $sql=new sql;
$user=new user; */
global $sql,$user;

if(!$reviews=$user->getReviews()){
	echo '<p class="main_products_list_null">Мы не нашли ни одного отзыва или оценки товара, оставленного Вами.<br>Чтобы оценить товар или оставить к нему отзыв, выберите товар в <a href="/catalog/" class="clean-filters underline">нашем каталоге</a>.</p>';
}
else{

	if(isset($_GET['success']))
		echo '<div class="form-alert success">Изменения успешно сохранены.</div>';
	if(isset($_GET['error']))
		echo '<div class="form-alert error">Произошла ошибка при сохранении данных.</div>';
?>

<?
}
?>