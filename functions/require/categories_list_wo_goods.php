<?
defined ('_DSITE') or die ('Access denied');
?>
<div class="main_container_body">
	<div class="main_content">
		<div class="main_container_header">
			<div class="breadcrumbs">
				<?$menu->breadcrumbs()?>
			</div>
			<div class="clr"></div>
			<h1><?=$current['h1']?></h1>
		</div>
		<div class="main_products_list_items">
			<?$content->getCategories();?>
		</div>
		<div class="clr"></div>
		<div class="main_content_container">
			<?=$current['content'];?>
		</div>
	</div>
</div>
