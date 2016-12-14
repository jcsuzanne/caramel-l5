<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Jenssegers\Agent\Agent;
use Cache;
use Illuminate\Support\Str;


class CaramelController extends Controller
{

    public  $viewData      = array(
      'jscontroller' => ''
    , 'metaTitle' => ''
    , 'metaDescription' =>''
    , 'menu' => null
    , 'manifest' => array()
    , 'view' => null
    , 'template' => 'templates/desktop'
    , 'h1InView' => false
    );
    public $isMobile = null; // user agent info
    public $shared;
    private $cached_request;
    public $cached_duration = 72 * HOUR_IN_SECONDS;
    public $agent;

    public function __construct(Request $request)
    {
        $this->cached_request = config('app.cache_enabled');
        $this->agent = new Agent();
        if($this->agent->isMobile() && !$this->agent->isTablet()):
            $this->viewData['template'] = 'templates/mobile';
            $this->isMobile = true;
        endif;
        if(!$this->cached_request):
            Cache::flush();
        endif;
        $dataShared = array(
            'global'=>$this->globalInfo(),
            'isMobile'=>$this->isMobile,
        );
        $this->shared = $dataShared;
        view()->share('shared',$this->shared);
    }

    static function makeURL($slug,$route,$lang = '')
    {
        if(isset($lang)):
            $lang = App::getLocale();
        endif;
        return LaravelLocalization::getLocalizedURL($lang,url($route.'/'.$slug));
    }

    public function globalInfo()
    {
        $global = array();
        $global['sitename'] = get_option('blogname');
        $global['sitedesc'] = get_option('blogdescription');
        $global['lang'] = config('app.locale');
        return $global;
    }

    //
    public function mainNavigation($request)
    {
        $menu = array();
        $args = array(
            'post_status' => 'publish'
        );
        $wp_menu = wp_get_nav_menu_items('main-navigation' , $args);
        $current = $request->url();
        $menu['home'] = array(
            'slug'=>trans('routes.home'),
            'url'=>LaravelLocalization::getURLFromRouteNameTranslated(config('app.locale'), 'routes.home'),
            'show'=>0,
            'active'=>0,
        );
        if($wp_menu):
            foreach($wp_menu as $item):
                $obj = (object) $item;
                $objectID = (int) $obj->object_id;
                $id = $obj->ID;
                $post = get_post($obj->object_id);
                $title = $obj->title;
                $slug = $post->post_name;
                $parent = $obj->menu_item_parent;
                if($parent != '0'):
                    $menu[$parent]['children'][$id] = array(
                        'slug'=>$slug,
                        'title'=>$title,
                        'url'=>$obj->url,
                        'active'=>0,
                        'show'=>1
                    );
                else:
                    $menu[$id] = array(
                        'slug'=>$slug,
                        'title'=>$title,
                        'url'=>$obj->url,
                        'active'=>0,
                        'show'=>1
                    );
                endif;
            endforeach;
        endif;
        return $menu;
    }

    public function generatePage($id,$path,$jscontroller)
    {
        $full = $wp_menu = wp_get_nav_menu_items($id);
        $html = '';
        foreach($full as $sub)
        {
            $pageID = $sub->object_id;
            $page = get_post($pageID);
            $fields = get_post_meta($pageID);
            $moduleType = $fields['module_type'][0];
            $content = Helpers::createModule($moduleType,$page,$fields);
            $html .= $content;
        }
        $datas = array(
            'controller'=>$this,
            'view'=>$html
        );
        //
        $params = array(
            'name'=>$id,
            'post_type'=>'page'
        );
        $query = new \WP_Query($params);
        $main = $query->get_posts()[0];
        $metas = Helpers::getMetadatas($main->ID);
        $content = View::make($path)->with('datas', $datas);
        $this->viewData['view'] = $content;
        $this->viewData['jscontroller'] = $jscontroller;
        $this->viewData['meta'] = $metas['title'] . ' - '. $this->shared['global']['sitename'];
        $this->viewData['metaDescription'] = $metas['description'];
    }

    public function doQuery($request, $params,$transient_name)
    {
        $params = $params;
        $prefix = 'handmade_';
        // GET PARAMS
        $paramsGET = $request->all();

        if(!empty($paramsGET)):
            if(isset($paramsGET['preview'])):
                $params['preview'] = true;
            endif;
        endif;

        if($this->cached_request):
            if(get_transient( $transient_name ) === false):
                $query = new \WP_Query($params);
                set_transient( $prefix.$transient_name , $query, $this->cached_duration );
            else:
                $query = get_transient( $prefix.$transient_name );
            endif;
        else:
            $query = new \WP_Query($params);
        endif;
        return $query;
    }

}