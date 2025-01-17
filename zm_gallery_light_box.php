<?php

/**
 * Plugin Name: WP Gallery LightBox Plus 
 * Plugin URI: https://www.azimiao.com
 * Description: 一个对WP自带相册增加LightBox特效的小插件
 * Version: 1.0.6
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

	$image_output = "";

	$columns = intval($atts['columns'] ?? 3);



	$selector = "gallery-{$instance}";
	$gallery_style = "
		<style type='text/css'>
			#{$selector} {
			 	flex: 1;
				columns: {$columns};
				column-gap: 2px;
  				padding-bottom: 0.9em;
			}
			/* Not fixed, just looks better in Safari(iOS,MacOS) */
			#{$selector} .avoidwebkitbug14137{
				border:1px solid transparent;
				break-inside: avoid;
				margin-bottom: 2px;
			}
			#{$selector} .gallery-item {
				break-inside: avoid;
				border:6px solid #fff;
				margin-top:0;
				box-shadow:0 0 0 1px #12376914,0 1px 1px #1237690a,0 3px 3px #12376908,0 6px 4px #12376905,0 11px 4px #12376903
			}


				#{$selector} a {
				display:block;
				text-decoration:unset;
				border:none !important;
			}

				
			#{$selector} img {
					display: block;
				    box-sizing:border-box;
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
				background: rgba(0, 0, 0, 0.5); /* 半透明背景 */
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
				width: 14px; /* 改成10px */
				height: 14px; /* 改成10px */
				border: 2px solid white;
				border-radius: 50%; /* 确保圆形 */
				display: inline-block;
				position: relative;
			}

			#{$selector} a:hover .magnifier-icon {
			transform: scale(1.25); /* 放大效果 */
			background: rgba(255, 255, 255, 0.7);
			}
		</style>\n\t\t";



	$image_output .= $gallery_style;

	$zmGLCConfig = get_option('zm_gallerylightbox_config');
	if (is_array($zmGLCConfig) && $zmGLCConfig['customCss'] != "") {
		$image_output .= "<style>" . $zmGLCConfig['customCss'] . "</style>";
	}

	$image_output .= "<div id='$selector' class='gallery gallery-columns-{$columns}'>\r\n";


	if (!isset($atts['link'])) {

		foreach ($ids as $id) {
			$image_output .= "<div class='avoidwebkitbug14137'>";
			$image_output .= "<figure class='gallery-item'>";
			$url_show = wp_get_attachment_image_src($id, $atts["size"] ?? $defaultImageStr, false);
			$url_real = get_attachment_link($id) ?? "";
			$image_output .= "<a href='$url_real' target='_blank'> <img src='$url_show[0]'/><div class='magnifier-icon' title='点击查看'></div></a>";
			$image_output .= "</figure>";
			$image_output .= "</div>";
		}
	} else if ($atts['link'] === 'file') {

		foreach ($ids as $id) {
			$image_output .= "<div class='avoidwebkitbug14137'>";
			$image_output .= "<figure class='gallery-item'>";
			$url_show = wp_get_attachment_image_src($id, $atts["size"] ?? $defaultImageStr, false);
			$url_real = wp_get_attachment_url($id);
			$image_output .= "<a href='$url_real' data-lightbox='gallery-$instance' ><img src='$url_show[0]' /><div class='magnifier-icon' title='点击查看'></div></a>";
			$image_output .= "</figure>";
			$image_output .= "</div>";
		}
	} else if ($atts['link'] === 'none') {
		foreach ($ids as $id) {
			$image_output .= "<div class='avoidwebkitbug14137'>";
			$image_output .= "<figure class='gallery-item'>";
			$url_show = wp_get_attachment_image_src($id, $atts["size"] ?? $defaultImageStr, false);
			$url_real = wp_get_attachment_url($id);
			$image_output .= "<img src='$url_show[0]'/>";
			$image_output .= "</figure>";
			$image_output .= "</div>";
		}
	} else {
		return $output;
	}

	$image_output .= "</div>";
	return $image_output;
}