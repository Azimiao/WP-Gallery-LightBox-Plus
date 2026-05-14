<?php

class GalleryLightBox_Admin{

    function __construct(){
        //创建菜单
        add_action("admin_menu",array($this,"init"));
    }

    function getOption(){
        //获取配置
        $options = get_option('zm_gallerylightbox_config');
        //判断
        if(!is_array($options))
        {
            $options['customCss'] = '';
            update_option('zm_gallerylightbox_config', $options);
        }
        return $options;
    }

    function init(){
        $galleryNoceFlag = false;
        //添加子菜单页面
        add_options_page("WP-Gallery-LightBox","WP-Gallery-LightBox","manage_options","zm_gallerylightbox_setting",array($this,"optionPage"));
        if(!empty($_POST["gallery-lightbox-save-nonce"]))
        {
            $galleryNoce =  $_POST["gallery-lightbox-save-nonce"];
            
            if(wp_verify_nonce( $galleryNoce, 'gallery-lightbox-nonce'))
            {
                //echo ">>>>>>>>>>>>>>>>>>True";
                $galleryNoceFlag = true;
            }else{
                //echo "<<<<<<<<<<<<<<<<<<False";
            }
        }
        $sCheck1 = is_admin() && isset($_POST[ 'zm_gallerylightbox_save' ]);
        $sCheck2 = is_admin() && isset($_POST[ 'zm_gallerylightbox_clear' ]);
        
        $options = $this->getOption();

        if(($sCheck1 || $sCheck2) && $galleryNoceFlag){
            if($sCheck1) {
                $customCss = $_POST['customCss'];
                $customCss = strip_tags($customCss);
                $options['customCss'] = stripslashes($customCss);
                update_option('zm_gallerylightbox_config', $options);
                echo "<div id='message' class='updated fade'><p><strong>数据已更新</strong></p></div>";
            
            }else if($sCheck2)
            {
                $options['customCss'] = "";
                update_option('zm_gallerylightbox_config', $options);
                echo "<div id='message' class='error fade'><p><strong>数据已清除</strong></p></div>";
            }
        }else{
            //die("安全检查失败");
        }

    }

    function optionPage(){

		$options = $this->getOption();

        ?>
        <style type="text/css">
        .zm-glb-admin .postbox { max-width: 800px; }
        .zm-glb-admin .postbox .inside { margin: 0; padding: 12px 16px; }
        .zm-glb-admin .zm-glb-steps { margin: 0; padding-left: 1.2em; }
        .zm-glb-admin .zm-glb-steps li { margin-bottom: 6px; line-height: 1.6; }
        .zm-glb-admin .zm-glb-css-label { display: block; font-weight: 600; margin-bottom: 8px; }
        .zm-glb-admin .zm-glb-actions { margin-top: 16px; display: flex; gap: 8px; align-items: center; }
        </style>

        <script type="text/javascript">
        jQuery(document).ready(function($){
            $('.zm-glb-admin .postbox .postbox-header').on('click', function(){
                $(this).closest('.postbox').toggleClass('closed');
            });
        });
        </script>

        <div class="wrap zm-glb-admin">

            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <form action="" method="post">

                <!-- 使用说明 -->
                <div class="postbox" style="margin-top: 20px;">
                    <div class="postbox-header" style="cursor: pointer;">
                        <h2 style="padding: 8px 12px; margin: 0;">使用说明</h2>
                    </div>
                    <div class="inside">
                        <ol class="zm-glb-steps">
                            <li>编辑文章或页面时选择左上角<strong>添加媒体</strong> → <strong>创建相册</strong>。</li>
                            <li>选择你喜欢的图像（多选），后点击左下角<strong>创建新相册</strong>，进入相册设置页面。</li>
                            <li>在右上角设置相册尺寸、排序，并将<strong>链接到</strong>选项修改为<strong>媒体文件</strong>。</li>
                            <li>点击插入相册，即可在文章中加入相册短代码。</li>
                        </ol>
                        <p class="description">请注意：本插件依赖 jQuery。</p>
                    </div>
                </div>

                <!-- 自定义CSS设置 -->
                <div class="postbox">
                    <div class="postbox-header" style="cursor: pointer;">
                        <h2 style="padding: 8px 12px; margin: 0;">自定义样式</h2>
                    </div>
                    <div class="inside">
                        <label class="zm-glb-css-label" for="zm-custom-css">自定义相册缩略图 CSS<span class="description">（不懂请留空）</span></label>
                        <textarea id="zm-custom-css" name="customCss" rows="10" class="widefat code"><?php echo esc_textarea(empty($options['customCss']) ? '' : $options['customCss']); ?></textarea>

                        <input type="hidden" name="gallery-lightbox-save-nonce" value="<?php echo wp_create_nonce('gallery-lightbox-nonce'); ?>" />

                        <div class="zm-glb-actions">
                            <button type="submit" name="zm_gallerylightbox_save" class="button button-primary">保存设置</button>
                            <button type="submit" name="zm_gallerylightbox_clear" class="button button-link-delete" onclick="return confirm('确定要清空自定义 CSS 吗？');">清空记录</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <?php

        }

    }

new GalleryLightBox_Admin();
?>
