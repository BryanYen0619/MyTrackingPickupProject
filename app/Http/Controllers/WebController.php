<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebController extends Controller
{
        // 瀏覽網站根目錄時提示訊息
        public function wellcome()
        {
                return '需帶入「社區 id」 及「使用者 id」，例：'. \URL::current() .'/community/1/user/1';
        }
}
