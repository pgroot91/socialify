<?php
namespace Socialify;
defined('ABSPATH') || die();

/**
 * ShortcodeLogin
 */
final class ShortcodeLogin
{
    /**
     * @var bool for check login page
     */
    public static $is_login_page = false;

    /**
     * The init
     */
    public static function init()
    {
      add_shortcode('socialify_login', function() {
        $data                = [];
        $data['login_items'] = [
          'email_standard' => [
            'url'     => wp_login_url(home_url()),
            'ico_url' => General::$plugin_dir_url . 'assets/svg/email.svg',
          ],
        ];

        foreach ($data['login_items'] as $key => $item) {
          $data['login_items'][ $key ]['class_array'] = ['socialify_shortcode_login__item', 'socialify_' . $key];
        }

        $data = apply_filters('socialify_shortcode_data', $data);

        ob_start();
        require_once __DIR__ . '/../templates/shortocde-btns.php';
        return ob_get_clean();
      });

        add_action('plugins_loaded', function (){
            add_action( 'wp_enqueue_scripts', [__CLASS__, 'assets'] );

            add_filter('socialify_shortcode_data', [__CLASS__, 'add_redirect_to']);

            if(get_option('socialify_login_page_show', 1)){
                add_filter('socialify_shortcode_data', [__CLASS__, 'filter_login_page']);
                add_action('login_form', [__CLASS__, 'add_to_login_page']);
                add_action('login_enqueue_scripts', [__CLASS__, 'assets_login_page']);
            }
        });
    }

    /**
     * add redirect to param for login url
     */
    public static function add_redirect_to($data)
    {
        if(empty($data['login_items'])){
            return $data;
        }

        $data_new = $data;
        foreach ($data['login_items'] as $key => $data_item){
            $data_new['login_items'][$key]['url'] = remove_query_arg('redirect_to', $data_item['url']);
            $redirect_to = self::get_redirect_to();
            $data_new['login_items'][$key]['url'] = add_query_arg('redirect_to', urlencode($redirect_to), $data_item['url']);
        }

        return $data_new;
    }

    /**
     * get_redirect_to
     */
    public static function get_redirect_to()
    {
        global $wp;
        $redirect_to = empty($_GET['redirect_to']) ? home_url( $wp->request ) : $_GET['redirect_to'];
        return apply_filters('socialify_redirect_to', $redirect_to);
    }


    public static function filter_login_page($data){
        if(self::$is_login_page){
            unset($data['login_items']['email_standard']);
        }
        return $data;
    }

    public static function add_to_login_page(){
        echo do_shortcode('[socialify_login]');
    }

    public static function assets_login_page(){

        self::$is_login_page = true; //hack for check login page
        wp_enqueue_style(
            'socialify-sc-style',
            $url = General::$plugin_dir_url . 'assets/style.css',
            $dep = array(),
            $ver = filemtime(General::$plugin_dir_path . '/assets/style.css')
        );
    }

    /**
     * assets
     */
    public static function assets()
    {
        wp_enqueue_style(
                'socialify-sc-style',
                $url = General::$plugin_dir_url . 'assets/style.css',
                $dep = array(),
                $ver = filemtime(General::$plugin_dir_path . '/assets/style.css')
        );
    }
}

ShortcodeLogin::init();