<?
defined ('_DSITE') or die ('Access denied');
?>
<div class="main_container_body clwg">
	<div class="main_content">
		<div class="main_container_header">
			<div class="breadcrumbs">
				<?$menu->breadcrumbs()?>
			</div>
			<div class="clr"></div>
			<h1><?=$current['h1']?></h1>
		</div>
		<div class="main_products_list_items mb">
			<?$content->getCategories();?>
		</div>
		<div class="clr"></div>
		<div class="main_products_list_items after_categories no-background">
			<h2>Все товары в этой категории</h2>
		</div>
		<div class="main_products_list_toppanel">
			<div class="main_products_list_toppanel_sort">
				<label>Сортировать по&nbsp;</label>
				<div class="select_default_container">
					<div class="select_default">
						<p class="select_default_option_selected"><?=$products_sort[get('sort')];?></p>
						<div class="items">
						<?
						foreach($products_sort as $_name=>$_name_ru)
							echo $_name!=null?'<p class="select_default_option'.($products_sort[get('sort')]==$_name_ru?' selected':'').'" data-value="'.$_name.'">'.$_name_ru.'</p>':'';
						?>
						</div>
						<span class="icon icon-arrow-down"></span>
					</div>
				</div>
			</div>
			<div class="main_products_list_toppanel_view">

			</div>
			<div class="main_products_filters_count">
				<button class="main_products_filters_count_button button_show"><span>Применить</span></button>
			</div>
		</div>
		<div class="clr"></div>
		<div class="main_products_list_items after_categories">
			<?$content->getGoods(null,'table',30);?>
		</div>
		<div class="clr"></div>
		<div class="main_content_container">
			<?=$current['content'];?>
		</div>
	</div>
</div>
