<?
defined ('_DSITE') or die ('Access denied');
?>
<div class="main_container_header">
	<div class="breadcrumbs">
		<?$menu->breadcrumbs()?>
	</div>
	<div class="clr"></div>
	<h1><?=$current['h1']?></h1>
</div>
<div class="main_container_body">
	<div class="main_container_header">
		<div class="breadcrumbs">
			<?$menu->breadcrumbs()?>
		</div>
		<div class="clr"></div>
		<h1><?=$current['h1']?></h1>
	</div>
	<?

	?>
	<div class="main_content">
		<div class="main_content_container">
			<?
				if(isset($_GET['search']))
					$content->search(get('search'));
				echo $current['content'];
			?>
		</div>
	</div>
</div>
