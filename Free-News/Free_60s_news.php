<?php


/*
Plugin Name: WP 60sNews
Plugin URI: http://www.wpon.cn/33737.html
Description: 完全免费的每日新闻自动发布工具，助力你的SEO！
Author: 夏柔公益
Version: 1.0.0
Author URI: http://www.aa1.cn/
*/

/*
    Copyright 夏柔公益.
    本插件完全免费，请勿售卖，感谢使用；
    希望大家的网站seo数据可以日益增长；
    
    使用教程：www.wpon.cn/33737.html 切勿删除 尊重原创
	部分内容可按需修改，如：每天60秒读懂世界分类、新标题等；
	使用不懂请联系夏柔QQ：15001904
	
 - 功能列表
    
    支持自定义发布新闻分类（注明）；
    
    支持自定义新闻标题（快捷简洁）；
    
    优化每日新闻的图片风格，增加了农历生成；
    
    增加百度热搜 + 知乎热搜；
    
    支持每日新闻图片保存到本地并发布文章；
    
    支持选择发布用户；
    
    支持选择发布分类；
    
    支持设置自定义尾巴标题；
    
    更多功能敬请期待！
    
    
*/

add_action('init', 'Free_60s_news');

function XiaRounews_plugin_action_links($links) {
    $settings_link = '<a href="options-general.php?page=XiaRounews_plugin_settings">设置</a>';
    array_unshift($links, $settings_link);
    return $links;
}

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'XiaRounews_plugin_action_links');

// 添加菜单
function XiaRounews_plugin_menu() {
    add_menu_page(
        '每日自动发布新闻 - 设置页',    // 页面标题
        'WP 60sNews',        // 菜单标题
        'manage_options',  // 用户权限
        'XiaRounews_plugin_settings', // 菜单ID
        'XiaRounews_plugin_settings_page', // 回调函数，显示设置页面
        'dashicons-admin-plugins', // 菜单图标
        100 // 菜单位置
    );
}
add_action('admin_menu', 'XiaRounews_plugin_menu');


// 设置页面回调函数
function XiaRounews_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h2>每日自动发布新闻 - 设置页</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('XiaRounews_plugin_settings');
            do_settings_sections('XiaRounews_plugin_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// 注册设置项
function XiaRounews_plugin_register_settings() {
    register_setting(
        'XiaRounews_plugin_settings', // 设置项的名称，与form中的action和settings_fields中的参数一致
        'XiaRounews_plugin_option',   // 选项名称
        'XiaRounews_plugin_sanitize'  // 用于验证和清理输入的回调函数
    );

    add_settings_section(
        'XiaRounews_plugin_general_section', // 区块ID
        '使用前介绍',                  // 区块标题
        'XiaRounews_plugin_general_section_cb', // 回调函数，显示区块的内容
        'XiaRounews_plugin_settings'        // 放置区块的页面ID
    );

    add_settings_field(
        'XiaRounews_plugin_text_field',     // 字段ID
        '设置秘钥保存',                 // 字段标题
        'XiaRounews_plugin_text_field_cb',  // 回调函数，显示字段的内容
        'XiaRounews_plugin_settings',       // 放置字段的页面ID
        'XiaRounews_plugin_general_section'  // 放置字段的区块ID
    );
    
    // 自定义标题
    add_settings_field(
        'XiaRounews_plugin_title_field',     // 字段ID
        '设置自定义尾巴标题',                 // 字段标题
        'XiaRounews_plugin_title_field_cb',  // 回调函数，显示字段的内容
        'XiaRounews_plugin_settings',       // 放置字段的页面ID
        'XiaRounews_plugin_general_section'  // 放置字段的区块ID
    );
    
    // 自定义图片url
    add_settings_field(
    'XiaRounews_plugin_url_field',     // 字段ID
    '自定义URL',                // 字段标题
    'XiaRounews_plugin_url_field_cb',  // 回调函数，显示字段的内容
    'XiaRounews_plugin_settings',      // 放置字段的页面ID
    'XiaRounews_plugin_general_section' // 放置字段的区块ID
);

    
    // 选择发布用户
    add_settings_field(
    'XiaRounews_plugin_user_field',       // 字段ID
    '选择发布用户',                    // 字段标题
    'XiaRounews_plugin_user_field_cb',    // 回调函数，显示字段的内容
    'XiaRounews_plugin_settings',          // 放置字段的页面ID
    'XiaRounews_plugin_general_section'    // 放置字段的区块ID
    );
    
    // 选择发布分类
    add_settings_field(
        'XiaRounews_plugin_category_field',   // 字段ID
        '选择发布分类',                 // 字段标题
        'XiaRounews_plugin_category_field_cb',  // 回调函数，显示字段的内容
        'XiaRounews_plugin_settings',          // 放置字段的页面ID
        'XiaRounews_plugin_general_section'    // 放置字段的区块ID
    );
    
    // 添加新的设置字段
    add_settings_field(
        'XiaRounews_plugin_select_field',   // 字段ID
        '选择上游接口（暂时只有官方）',                    // 字段标题
        'XiaRounews_plugin_select_field_cb', // 回调函数，显示字段的内容
        'XiaRounews_plugin_settings',        // 放置字段的页面ID
        'XiaRounews_plugin_general_section'   // 放置字段的区块ID
    );
   
}
add_action('admin_init', 'XiaRounews_plugin_register_settings');

// 在添加字段的地方加上
function XiaRounews_plugin_general_section_cb() {
    
    $protocol_bt_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
	$host_bt_url = $_SERVER['HTTP_HOST'];
	$base_bt_url = $protocol_bt_url . "://" . $host_bt_url;
			
    echo '<p>您好，很高兴您使用了本插件，但还差一步就可以完全使用了，需要您前往 <a href="https://60s.aa1.cn/60s-api/mytoken" target="_blank">获取秘钥</a>，复制你的免费秘钥并粘贴到下方【<code>设置秘钥</code>】选项里即可。</p>';
    echo '<h2><a name="使用教程" class="reference-link"></a><span class="header-link octicon octicon-link"></span>使用教程</h2><p>您可查阅：<a href="https://www.wpon.cn/33737.html">https://www.wpon.cn/33737.html</a> 查看使用教程；</p>
<ol>
<li>第一步：请您先获取免费的Token秘钥并粘贴到下方设置秘钥框里；</li><li>第二步：保存选项，开始使用！</li><li>第三步：请您前往 宝塔 - 计划任务 - 添加计划任务，访问URL填写：【<code>'.$base_bt_url.'/60s-news</code>】；</li><li>执行计划任务，开启自动化发布新闻之旅！</li></ol>

';
    // submit_button('保存设置', 'primary', 'submit', false);
}

// 设置秘钥字段回调函数
function XiaRounews_plugin_text_field_cb() {
    $option_value = get_option('XiaRounews_plugin_option');
    echo '<input type="text" name="XiaRounews_plugin_option[apitoken]" value="' . esc_attr($option_value['apitoken']) . '" />';
}


// 设置标题字段回调函数
function XiaRounews_plugin_title_field_cb() {
    $option_value = get_option('XiaRounews_plugin_option');
    $default_value = '每天60秒读懂全世界！';
    
    $title_value = isset($option_value['titles']) ? $option_value['titles'] : $default_value;
    
    if (empty($title_value)) {
        $title_value = $default_value;
    }

    echo '<input type="text" name="XiaRounews_plugin_option[titles]" value="' . esc_attr($title_value) . '" />';
}

// 自定义URL字段回调函数
function XiaRounews_plugin_url_field_cb() {
    $option_value = get_option('XiaRounews_plugin_option');
    $custom_url_value = isset($option_value['custom_url']) ? $option_value['custom_url'] : 'https://60s.aa1.cn/60s-api/img/';

    echo '<input type="text" name="XiaRounews_plugin_option[custom_url]" value="' . esc_attr($custom_url_value) . '" />';
}

// 在 functions.php 或插件文件中添加以下代码块
function XiaRounews_plugin_user_field_cb() {
    $option_value = get_option('XiaRounews_plugin_option');
    $selected_user = isset($option_value['user_id']) ? $option_value['user_id'] : 1; // 默认为用户ID为1

    // 获取所有用户
    $users = get_users();
    ?>
    <select id="XiaRounews_plugin_user_field" name="XiaRounews_plugin_option[user_id]">
        <?php
        foreach ($users as $user) {
            ?>
            <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($selected_user, $user->ID); ?>><?php echo esc_html($user->display_name); ?></option>
            <?php
        }
        ?>
    </select>
    <?php
}

// 选择发布分类字段回调函数
function XiaRounews_plugin_category_field_cb() {
    $option_value = get_option('XiaRounews_plugin_option');
    $selected_category = isset($option_value['category_field']) ? $option_value['category_field'] : '1';

    // 获取所有文章分类
    $categories = get_categories();

    ?>
    <select id="XiaRounews_plugin_category_field" name="XiaRounews_plugin_option[category_field]">
        <?php
        foreach ($categories as $category) {
            ?>
            <option value="<?php echo esc_attr($category->term_id); ?>" <?php selected($selected_category, $category->term_id); ?>>
                <?php echo esc_html($category->name); ?>
            </option>
            <?php
        }
        ?>
    </select>
    <?php
}


// 选择项字段回调函数
function XiaRounews_plugin_select_field_cb() {
    $option_value = get_option('XiaRounews_plugin_option');
    $selected_value2 = $selected_value = isset($option_value['select_field']) ? $option_value['select_field'] : '1';
    $custom_value = isset($option_value['custom_value']) ? $option_value['custom_value'] : '';

    ?>
    <select id="XiaRounews_plugin_select_field" name="XiaRounews_plugin_option[select_field]">
        <option value="https://60s.aa1.cn/60s-api/freenews/" <?php selected($selected_value, 'https://60s.aa1.cn/60s-api/freenews/'); ?>>官方新闻接口</option>
        <!--<option value="2" <php selected($selected_value, '2'); ?>>自定义</option>-->
    </select>


    <div id="custom_input" style="display: <?php echo ($selected_value === '2') ? 'block' : 'none'; ?>;">
        <label for="XiaRounews_plugin_custom_input">其它接口：</label>
        <input type="text" id="XiaRounews_plugin_custom_input" name="XiaRounews_plugin_option[custom_value]" value="<?php echo esc_attr($custom_value); ?>" />
    </div>

    <script>
        // JavaScript 代码，根据选择切换显示和隐藏输入框
        document.addEventListener('DOMContentLoaded', function () {
            var selectField = document.getElementById('XiaRounews_plugin_select_field');
            var customInput = document.getElementById('custom_input');

            // 初始化时检查选择的值
            if (selectField.value === '2') {
                customInput.style.display = 'block';
            }

            // 监听选择框的变化
            selectField.addEventListener('change', function () {
                if (this.value === '2') {
                    customInput.style.display = 'block';
                } else {
                    customInput.style.display = 'none';
                }
            });
        });
    </script>
    <?php
}



// 输入验证和清理函数
function XiaRounews_plugin_sanitize($input) {
    $sanitized_input = array();

    // 清理文本字段
    if (isset($input['apitoken'])) {
        $sanitized_input['apitoken'] = sanitize_text_field($input['apitoken']);
    }
    // 清理文本字段
    if (isset($input['titles'])) {
        $sanitized_input['titles'] = sanitize_text_field($input['titles']);
    }
    // 清理文本字段
    if (isset($input['custom_url'])) {
        $sanitized_input['custom_url'] = sanitize_text_field($input['custom_url']);
    }

    // 清理选择项字段
    if (isset($input['select_field'])) {
        $sanitized_input['select_field'] = sanitize_text_field($input['select_field']);
    }

    // 清理自定义字段
    if (isset($input['custom_value'])) {
        $sanitized_input['custom_value'] = sanitize_text_field($input['custom_value']);
    }
    
    
    // 清理选择发布用户字段
    if (isset($input['user_id'])) {
        $sanitized_input['user_id'] = absint($input['user_id']);
    }
    
    if (isset($input['category_field'])) {
        $sanitized_input['category_field'] = absint($input['category_field']);
    }

    return $sanitized_input;
}



function Free_60s_news() {
    
    function get_selected_value() {
    $option_value = get_option('XiaRounews_plugin_option');
    $selected_value = isset($option_value['select_field']) ? $option_value['select_field'] : 'https://60s.aa1.cn/60s-api/freenews/';
    $custom_url_value = isset($option_value['custom_url']) ? $option_value['custom_url'] : 'https://60s.aa1.cn/60s-api/img/';

    

    return esc_url($selected_value);
    }
    
    
	if (strpos($_SERVER['REQUEST_URI'], '/60s-news') !== false) {
		
		date_default_timezone_set('Asia/Shanghai');
		// 获取当前日期的星期几（数字表示）
		$dayOfWeekNumber = date('N');
		// 将数字转换为中文星期名称
		$chineseDays = array('一', '二', '三', '四', '五', '六', '日');
		$chineseDayOfWeek = $chineseDays[$dayOfWeekNumber - 1];
		// 星期日数据
		$TodayOfWeek =  "星期".$chineseDayOfWeek;

    	 // 获取文本字段的值
            $option_value = get_option('XiaRounews_plugin_option');
            $text_field_value = isset($option_value['apitoken']) ? $option_value['apitoken'] : '';
            $titles_field_value = isset($option_value['titles']) ? $option_value['titles'] : '';
            
        
            // 在你的输出中使用文本字段的值
            $title_custom = date("m月d日") . "，". $TodayOfWeek . "，$titles_field_value";
    
		// - 发布文章分类  --- 
            $option_value = get_option('XiaRounews_plugin_option');
            $selected_category = isset($option_value['category_field']) ? $option_value['category_field'] : '1';
            $term_taxonomy_id = $selected_category;
            $term = get_term($term_taxonomy_id, 'category');
            $Classification_custom = $term ? $term->name : '默认分类';
		$ch = curl_init();
		// 设置curl选项
		curl_setopt($ch, CURLOPT_URL, get_selected_value()."/?token=".get_option('XiaRounews_plugin_option')['apitoken']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 如果您的服务器没有配置好SSL证书，请谨慎使用此选项
		// 执行并获取结果
		$result = curl_exec($ch);
		// 关闭curl连接
		curl_close($ch);
		// 将获取的内容解析为JSON
		$new_data = json_decode($result);
		if ($new_data && isset($new_data->news) && is_array($new_data->news) && count($new_data->news) > 0) {
			$news_item = $new_data->news[0];
			// 获取图片URL
// 			$image_url = $news_item->image;
            $image_url = get_option('XiaRounews_plugin_option')['custom_url'];
			// 保存图片到本地 方法一 不推荐
			$image_filename = date('Y-m-d') . '.png';
			// 重命名为年月日.jpg
			$directory_path = 'wp-content/uploads/60s/';
			// 检查目录是否存在，不存在则创建
			if (!is_dir($directory_path)) {
				mkdir($directory_path, 0777, true);
				// 注意设置适当的权限，0777表示最大权限
			}
			$image_path = $directory_path . '/' . $image_filename;
			// 请替换为你实际的目录路径
			file_put_contents($image_path, file_get_contents($image_url));
			// 保存图片路径到数据库
			$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
			$host = $_SERVER['HTTP_HOST'];
			$base_url = $protocol . "://" . $host;
			$image_url_for_db = 'wp-content/uploads/60s/' . $image_filename;
			$news_item = $new_data->news[0];
			$title = $title_custom;
			// 生成新的标题
			$url = $news_item->url;
			$image = get_option('XiaRounews_plugin_option')['custom_url'];
			$content = $news_item->title;
			// 使用新接口返回的 title 和 hint
			require_once(ABSPATH . 'wp-config.php');
			global $wpdb;
			date_default_timezone_set('PRC');
			$post_tag_arr = array();
			$term_taxonomy_id = $wpdb->get_row("SELECT tt.term_taxonomy_id from $wpdb->terms t join $wpdb->term_taxonomy tt on t.term_id = tt.term_id where t.name = '$Classification_custom' and tt.taxonomy = 'category' ")->term_taxonomy_id;
			if (!$term_taxonomy_id) {
				$wpdb->query("insert into $wpdb->terms (name,term_group)VALUES('$Classification_custom','0')");
				$category_id = $wpdb->insert_id;
				$wpdb->query("insert into $wpdb->term_taxonomy (term_id,taxonomy,description,parent,count)VALUES($category_id,'category','','0','1')");
				$term_taxonomy_id = $wpdb->insert_id;
			}
			$post_tag_arr[] = $term_taxonomy_id;
			$html = '<img src="'.$base_url.'/'.$image_url_for_db.'"><br>'.$content;
			$posts = $wpdb->get_row("SELECT id from $wpdb->posts where post_title = '$title' ");
			if (!$posts) {
				$now = current_time('mysql');
				$now_gmt = current_time('mysql', 1);
				$wpdb->insert(
				            $wpdb->posts,
				            array(
				                'post_author' => get_option('XiaRounews_plugin_option')['user_id'],
				                'post_date' => $now,
				                'post_date_gmt' => $now_gmt,
				                'post_content' => $html,
				                'post_title' => $title,
				                'post_excerpt' => '',
				                'post_status' => 'publish',
				                'comment_status' => 'open',
				                'ping_status' => 'open',
				                'post_password' => '',
				                'post_name' => $title,
				                'to_ping' => '',
				                'pinged' => '',
				                'post_modified' => $now,
				                'post_modified_gmt' => $now_gmt,
				                'post_content_filtered' => '',
				                'post_parent' => '0',
				                'guid' => '', //文章链接 插入后修改
				                'menu_order' => '0',
				                'post_type' => 'post',
				                'post_mime_type' => '',
				                'comment_count' => '0',
				            )
				        );
				$insertid = $wpdb->insert_id;
				$post_guid = get_option('home') . '/?p=' . $insertid;
				$wpdb->query(" UPDATE $wpdb->posts SET guid=$post_guid where id = $insertid ");
				$sql = " INSERT INTO $wpdb->term_relationships (object_id,term_taxonomy_id,term_order) VALUES ";
				foreach ($post_tag_arr as $key => $value) {
					$sql .= "($insertid, $value, '0'),";
				}
				$wpdb->query(rtrim($sql, ","));
				// 输出JSON状态
				$response = array(
				            'code' => '200',
				            'status' => 'success',
				            'message' => '文章发布成功',
				            'post_id' => $insertid,
				            'post_guid' => $post_guid,
				        );
				echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			} else {
				// 如果文章已存在，也输出JSON状态
				$response = array(
				            'code' => '500',
				            'status' => 'error',
				            'message' => '文章已存在，无需重复发布',
				            'post_id' => $posts->id,
				            'post_guid' => get_permalink($posts->id),
				        );
				echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			}
		}
		exit();
	}
	

}


?>