<div class="main_container_body row">
	<div class="main_content col-lg-12 col-xs-12">
		<div class="main_container_header">
			<div class="breadcrumbs">
				<?$menu->breadcrumbs()?>
			</div>
			<div class="clr"></div>
			<h1><?=$current['h1']?></h1>
		</div>
		<?
			if($menu->isParent($current['menu'])||$menu->nodes_id[$current['menu']]['parent']!=0){
		?>
			<div class="main_products_filters" style="border-right:none; display: none">
				<div class="main_products_filters_block" style="padding:0;"></div>
				<div class="main_products_filters_block" style="border-right:1px solid rgba(160,160,160,.2);">
					<?
						$menu->displayLeftSubMenu();
					?>
				</div>
			</div>
		<?
		}
		?>
		<div class="main_content_container">
			<?=$current['content'];?>
		</div>
	</div>
</div>
