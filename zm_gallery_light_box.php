<?php

/**
 * Plugin Name: WP Gallery LightBox Plus 
 * Plugin URI: https://www.azimiao.com
 * Description: 一个对WP自带相册增加LightBox特效的小插件
 * Version: 1.0.7
 * Author: WildRabbit
 * Author URI: https://www.azimiao.com
 * License: GPL
 */

/*  Copyright 2018  azimiao  (email : admin@azimiao.com)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (is_admin()) {
	require_once("zm_gallery_light_admin.php");
}

$defaultImageStr = "zm-gallery-thumb";

add_action('wp_enqueue_scripts', 'RegNeedScripts');
add_filter('post_gallery', 'custom_gallery_output', 10, 2);

add_image_size( $defaultImageStr, 320, 0, false);


function RegNeedScripts()
{
	$LightBoxjsPath = plugins_url("js", __FILE__);
	$LightBoxCssPath = plugins_url("css", __FILE__);

	wp_register_script('zmlightboxjs', "$LightBoxjsPath/lightbox.js", array("jquery"));
	wp_enqueue_script('zmlightboxjs');
	wp_register_style('zmlightboxcss', "$LightBoxCssPath/lightbox.css");
	wp_enqueue_style('zmlightboxcss');
}

function custom_gallery_output($output, $atts)
{

	global $defaultImageStr;

	static $instance = 0;

	$instance++;

	$ids = isset($atts['ids']) ? explode(',', $atts['ids']) : [];
	if (empty($ids)) {
		return '';
	}

	$columns = intval($atts['columns'] ?? 3);
	$count = count($ids);
	$selector = "gallery-{$instance}";

	// Determine layout mode: horizontal row vs masonry
	$is_single_row = ($count <= $columns);

	$gallery_style = "
		<style type='text/css'>
			#{$selector} {
				display: flex;
				flex-wrap: wrap;
				gap: 4px;
				padding: 2px 2px 0.9em 2px;
			}

			#{$selector} .gallery-item {
				break-inside: avoid;
				border: 6px solid #fff;
				margin: 0;
				box-shadow: 0 0 0 1px #12376914, 0 1px 1px #1237690a, 0 3px 3px #12376908, 0 6px 4px #12376905, 0 11px 4px #12376903;
			}

			#{$selector} a {
				display: block;
				text-decoration: unset;
				border: none !important;
			}

			#{$selector} img {
				display: block;
				box-sizing: border-box;
				width: 100%;
				max-width: 100%;
				height: auto;
				margin: 0;
				padding: 0;
				border: none;
			}

			#{$selector} .magnifier-icon {
				position: absolute;
				box-sizing: border-box;
				bottom: 10px;
				right: 10px;
				width: 14px;
				height: 14px;
				background: rgba(0, 0, 0, 0.5);
				border-radius: 50%;
				display: flex;
				align-items: center;
				justify-content: center;
				cursor: pointer;
				transition: transform 0.3s ease, background 0.3s ease;
			}

			#{$selector} .magnifier-icon::before {
				content: '';
				box-sizing: border-box;
				width: 14px;
				height: 14px;
				border: 2px solid white;
				border-radius: 50%;
				display: inline-block;
				position: relative;
			}

			#{$selector} a:hover .magnifier-icon {
				transform: scale(1.25);
				background: rgba(255, 255, 255, 0.7);
			}

			/* Single-row horizontal layout */
			#{$selector}.zm-gallery-row .zm-gallery-col {
				flex: 1 1 0%;
				min-width: 0;
			}

			/* Masonry column layout */
			#{$selector}.zm-gallery-masonry {
				align-items: flex-start;
			}

			#{$selector}.zm-gallery-masonry .zm-gallery-col {
				flex: 1 1 0%;
				min-width: 0;
				display: flex;
				flex-direction: column;
				gap: 4px;
			}
		</style>\n\t\t";

	$image_output = $gallery_style;

	// Custom CSS from admin settings
	$zmGLCConfig = get_option('zm_gallerylightbox_config');
	if (is_array($zmGLCConfig) && $zmGLCConfig['customCss'] != "") {
		$image_output .= "<style>" . $zmGLCConfig['customCss'] . "</style>";
	}

	// Determine link mode
	$link_mode = $atts['link'] ?? 'default';
	if (!in_array($link_mode, ['default', 'file', 'none'], true)) {
		// fallback for unknown link= value: let WP handle it
		if (isset($atts['link'])) {
			return $output;
		}
		$link_mode = 'default';
	}

	// Build individual image items
	$items = [];
	foreach ($ids as $id) {
		$url_show = wp_get_attachment_image_src($id, $atts["size"] ?? $defaultImageStr, false);
		if (!$url_show) continue;

		$inner = '';
		if ($link_mode === 'none') {
			$inner = "<img src='{$url_show[0]}'/>";
		} else {
			if ($link_mode === 'file') {
				$url_link = wp_get_attachment_url($id);
				$link_attrs = "href='{$url_link}' data-lightbox='gallery-{$instance}'";
			} else {
				// default: link to attachment page
				$url_link = get_attachment_link($id) ?? "";
				$link_attrs = "href='{$url_link}' target='_blank'";
			}
			$inner = "<a {$link_attrs}><img src='{$url_show[0]}'/><div class='magnifier-icon' title='点击查看'></div></a>";
		}

		$items[] = "<figure class='gallery-item'>{$inner}</figure>";
	}

	$actual_count = count($items);
	if ($actual_count === 0) {
		return '';
	}

	if ($actual_count <= $columns) {
		// Single-row: each image is a direct flex child
		$layout_class = 'zm-gallery-row';
		$image_output .= "<div id='{$selector}' class='gallery gallery-columns-{$columns} {$layout_class}'>\r\n";
		foreach ($items as $item) {
			$image_output .= "<div class='zm-gallery-col'>{$item}</div>";
		}
	} else {
		// Masonry: distribute items into columns, filling column-by-column (top to bottom, left to right)
		$layout_class = 'zm-gallery-masonry';
		$image_output .= "<div id='{$selector}' class='gallery gallery-columns-{$columns} {$layout_class}'>\r\n";

		// Initialize columns
		$col_items = array_fill(0, $columns, []);

		// Distribute items column-by-column (top to bottom, left to right).
		// First (count % columns) columns get one extra item.
		$base = intval(floor($actual_count / $columns));
		$remainder = $actual_count % $columns;
		$idx = 0;
		for ($c = 0; $c < $columns; $c++) {
			$size = ($c < $remainder) ? ($base + 1) : $base;
			for ($r = 0; $r < $size; $r++) {
				$col_items[$c][] = $items[$idx];
				$idx++;
			}
		}

		foreach ($col_items as $col) {
			$image_output .= "<div class='zm-gallery-col'>";
			foreach ($col as $item) {
				$image_output .= $item;
			}
			$image_output .= "</div>";
		}
	}

	$image_output .= "</div>";
	return $image_output;
}