<?php   namespace App\Modules;

use Illuminate\Support\ServiceProvider;
use Request;

class ModuleServiceProvider extends ServiceProvider{

    public function register(){}

    public function boot(){
        //Load cai array modules trong file module.php trong thu muc config
        $modules = config('module.modules');

        $mod = $modules['site'];
        //Detect xem co phai la Backend route hay khong
        if(Request::is('admin') || Request::is('admin/*')){
            $mod = $modules['admin'];
        }

        //Load file route.php tuong ung cua tung module
        if(file_exists(__DIR__ . '/' . $mod . '/routes.php')){
            include __DIR__ . '/' . $mod . '/routes.php';
        }

        //Load cac file template tuong ung trong tung module
        if(is_dir(__DIR__ . '/' . $mod . '/Views')){
            $this->loadViewsFrom(__DIR__ . '/' . $mod . '/Views', $mod);
        }
    }
}
?>