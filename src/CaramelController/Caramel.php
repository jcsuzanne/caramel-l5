<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

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
      );
      public  $isMobile     = null; // user agent info

    public function home(Request $request)
    {

        $this->viewData['jscontroller'] = 'home';
        $this->viewData['metaTitle'] = 'title';
        $this->viewData['metaDescription'] = 'desc';
        $this->viewData['view'] = view('content.home')->render();

        return $this->viewData;
    }

}
