<?
defined ('_DSITE') or die ('Access denied');
?>
<div class="main_container_body">
	<div class="left_sidebar">
		<div class="nav">
			<div class="nav_container">
				<div class="nav_container_inner">
					<div class="nav_wrapper">
						<ul>
							<li id="menu_id_2000000000" class="has_sublevel">
								<a href="/catalog/" title="Каталог"><span class="menu-item-parent" style="font-size: .8em!important;">Каталог товаров</span></a>
							</li>
							<li id="menu_id_1000000000" class=""><a href="/" title="Главная"><span class="menu-item-parent" style="font-size: 1em;">Главная</span></a></li>
							<li id="nav_more" class="has_sublevel" style="display: none;"><a href="#"><span style="font-size: 1em;">Ещё</span></a><ul class="nav_sublevel" style="display: none;">&nbsp;</ul></li>
						</ul>
						<?
							//$menu->display('top-catalog',0,false);
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
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
