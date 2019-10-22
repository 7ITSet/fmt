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
	<div class="main_content wlb">
        <div class="main_container_header">
            <div class="breadcrumbs">
                <?$menu->breadcrumbs()?>
            </div>
            <div class="clr"></div>
            <h1><?=$current['h1']?></h1>
        </div>
        <div class="main_products_filters">
            <div class="main_products_filters_block">
                <div class="main_products_filters_count">
                    <p>Выбрано <span>{products_count}</span> товаров</p>
                    <button class="main_products_filters_count_button button_reset"><span>Сбросить</span></button>
                    <button class="main_products_filters_count_button button_show"><span>Показать</span></button>
                </div>
            </div>
            <div class="main_products_filters_block">
                <?
                $menu->displayLeftCat();
                ?>
            </div>
            {attributes}
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
		</div>
		<div class="clr"></div>
		<div class="main_products_list_items">
			<?$content->getGoods();?>
		</div>
		<div class="clr"></div>
		<div class="main_content_container">
			<?=$current['content'];?>
		</div>
	</div>
</div>	