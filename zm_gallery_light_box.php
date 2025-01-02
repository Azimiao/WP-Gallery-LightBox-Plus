<?php
/**
 * Plugin Name: WP Gallery LightBox Plus 
 * Plugin URI: https://www.azimiao.com
 * Description: 一个对WP自带相册增加LightBox特效的小插件
 * Version: 1.0.4
 * Author: azimiao(野兔#梓喵出没)
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

if(is_admin())
{
	require_once("zm_gallery_light_admin.php");
}

add_action( 'wp_enqueue_scripts', 'RegNeedScripts' );
add_filter('post_gallery', 'custom_gallery_output', 10, 2);



function RegNeedScripts(){
	$LightBoxjsPath = plugins_url("js",__FILE__);
	$LightBoxCssPath = plugins_url("css",__FILE__);

	wp_register_script( 'zmlightboxjs', "$LightBoxjsPath/lightbox.js",array("jquery") );
	wp_enqueue_script( 'zmlightboxjs' );//挂载脚本
	wp_register_style( 'zmlightboxcss', "$LightBoxCssPath/lightbox.css" );
	wp_enqueue_style( 'zmlightboxcss' );
}

function custom_gallery_output($output, $atts) {

	static $instance = 0;
	$instance++;

    // 检查是否是 file 类型的相册
    if (isset($atts['link']) && $atts['link'] === 'file') {
        $ids = isset($atts['ids']) ? explode(',', $atts['ids']) : [];
        if (empty($ids)) {
            return '';
        }

		$image_output ="";

		$columns = intval( $atts['columns'] ?? 3);
		$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
		


		$selector = "gallery-{$instance}";
		$gallery_style = "
		<style type='text/css'>
			#{$selector} {
				display: flex;
				flex-wrap: wrap;
				gap: 5px;
			}
			#{$selector} .gallery-item {
				flex: 1 1 calc({$itemwidth}% - 6px);
				max-width: calc({$itemwidth}% - 6px);
				box-sizing: border-box;
				border:6px solid #fff;
				margin: 1px;
				box-shadow:0 0 0 1px #12376914,0 1px 1px #1237690a,0 3px 3px #12376908,0 6px 4px #12376905,0 11px 4px #12376903
			}
			#{$selector} img {
				    box-sizing:border-box;
					width: 100%;
					max-width: 100%;
					height: auto;
					margin: 0;
					padding: 0;
					border: none;
			}
			#{$selector} .gallery-caption {
				margin-left: 0;
			}
			/* see gallery_shortcode() in wp-includes/media.php */
		</style>\n\t\t";



		$image_output .= $gallery_style;

		$zmGLCConfig = get_option('zm_gallerylightbox_config');
		if(is_array($zmGLCConfig) && $zmGLCConfig['customCss'] != "")
		{
			$image_output .= "<style>" . $zmGLCConfig['customCss'] . "</style>";
		}

		$image_output .= "<div id='$selector' class='gallery gallery-columns-{$columns}'>\r\n";

        foreach ($ids as $id) {
			 $image_output .= "<figure class='gallery-item'>";
			$url_show = wp_get_attachment_image_src($id,$atts["size"] ?? "thumbnail",false);
            $url_real = wp_get_attachment_url($id);
            $image_output .= "<a href='$url_real' data-lightbox='gallery-$instance'><img src='$url_show[0]' /></a>";
			$image_output .= "</figure>";
		}

		$image_output .= "</div>";
        return $image_output;
    }

    // 如果不是 file 类型，返回默认行为
    return $output;
}


