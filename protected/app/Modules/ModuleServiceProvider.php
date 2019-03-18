<?php
namespace App\Modules;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;

class ModuleServiceProvider extends RouteServiceProvider{

    protected $namespace = '';

    protected $mapWhat = '';


    public function register(){}

    public function boot(){

        $this->setNamespace();

        parent::boot();
    }

    /**
     * Override the map() function of Illuminate\Foundation\Support\Providers\RouteServiceProvider
     * it will be call by loadRoutes() function
     *
     * @return void
     */
    public function map(){
        $modules = config('modules');

        switch($this->mapWhat){
            case $modules['backend']['folder']:
                $this->mapBackend($modules['backend']);
                break;

            case $modules['api']['folder']:
                $this->mapApi($modules['api']);
                break;

            case $modules['frontend']['folder']:
                $this->mapFrontend($modules['frontend']);
                break;

            default:
        }
    }

    /**
     * Set the corresponding namspace based on the prefix url
     *
     * @return void
     */
    private function setNamespace(){

        $modules = config('modules');

        if(request()->is($modules['backend']['prefix_url']) || request()->is($modules['backend']['prefix_url'] . '/*')){
            $this->namespace = join('\\', ['App', 'Modules', $modules['backend']['folder'], 'Controllers']);
            $this->mapWhat = $modules['backend']['folder'];
        }
        elseif(request()->is($modules['api']['prefix_url']) || request()->is($modules['api']['prefix_url'] . '/*')){
            $this->namespace = join('\\', ['App', 'Modules', $modules['api']['folder'], 'Controllers']);
            $this->mapWhat = $modules['api']['folder'];
        }
        else{
            $this->namespace = join('\\', ['App', 'Modules', $modules['frontend']['folder'], 'Controllers']);
            $this->mapWhat = $modules['frontend']['folder'];
        }
    }

    /**
     * Mapping frontend routes and views
     *
     * @param array $mod
     * @return void
     */
    protected function mapFrontend(array $mod){
        $view_dir = implode(DIRECTORY_SEPARATOR, [__DIR__,  $mod['folder'], 'Views']);
        $route_file = implode(DIRECTORY_SEPARATOR, [__DIR__,  $mod['folder'], 'Routes', 'web.php']);

        $middleware = ['web'];
        if(is_array($mod['group_middleware']) && !empty($mod['group_middleware'])){
            $middleware = array_merge($middleware, $mod['group_middleware']);
        }

        Route::middleware($middleware)
            ->namespace($this->namespace)
            ->group($route_file);

        if(is_dir($view_dir)){
            $this->loadViewsFrom($view_dir, $mod['folder']);
        }
    }

    /**
     * Mapping backend routes and views
     *
     * @param array $mod
     * @return void
     */
    protected function mapBackend(array $mod){
        $view_dir = implode(DIRECTORY_SEPARATOR, [__DIR__,  $mod['folder'], 'Views']);
        $route_file = implode(DIRECTORY_SEPARATOR, [__DIR__,  $mod['folder'], 'Routes', 'web.php']);

        $middleware = ['web'];
        if(is_array($mod['group_middleware']) && !empty($mod['group_middleware'])){
            $middleware = array_merge($middleware, $mod['group_middleware']);
        }

        Route::middleware($middleware)
            ->prefix($mod['prefix_url'])
            ->namespace($this->namespace)
            ->group($route_file);

        if(is_dir($view_dir)){
            $this->loadViewsFrom($view_dir, $mod['folder']);
        }
    }

    /**
     * Mapping api route files
     *
     * @param array $mod
     * @return void
     */
    protected function mapApi(array $mod){
        $route_dir = implode(DIRECTORY_SEPARATOR, [__DIR__, $mod['folder'], 'Routes']);
        $entries = scandir($route_dir);
        foreach($entries as $f){
            if($f == '.' || $f == '..')
                continue;

            $route_file = implode(DIRECTORY_SEPARATOR, [__DIR__, $mod['folder'], 'Routes', $f]);
            $b = explode('.', $f);

            $middleware = [];
            if(is_array($mod['group_middleware']) && !empty($mod['group_middleware'])){
                $middleware = array_merge($middleware, $mod['group_middleware']);
            }

            Route::prefix($mod['prefix_url'] . '/' . $b[0])
                ->middleware($middleware)
                ->namespace($this->namespace)
                ->group($route_file);
        }
    }

}