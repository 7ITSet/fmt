<div class="main_container_header">
	<div class="breadcrumbs">
		<?$menu->breadcrumbs()?>
	</div>
	<div class="clr"></div>
	<h1><?=$current['h1']?></h1>
</div>
<div class="main_container_body">
<?
	if($menu->isParent($current['menu'])){
?>	
	<div class="main_products_filters">
		<div class="main_products_filters_block">
			<div class="main_products_filters_count">
				<p>Выберите категорию</p>
			</div>
		</div>
		<div class="main_products_filters_block">
			<?
				$menu->displayLeftSubMenu();
			?>
		</div>
	</div>
<?
}
?>
	<div class="main_content wlb">
		<div class="main_content_container">
			<?=$current['content'];?>
		</div>
	</div>
</div>	