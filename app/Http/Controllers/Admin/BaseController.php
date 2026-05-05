<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class BaseController extends \Illuminate\Routing\Controller
{
    use AuthorizesRequests, ValidatesRequests;
    
    /**
     * The middleware registered on the controller.
     *
     * @var array
     */
    protected $middleware = ['auth:admin'];
    
    /**
     * Register middleware on the controller.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function middleware($middleware, array $options = [])
    {
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }
        
        foreach ($middleware as $m) {
            $this->middleware[] = $m;
        }
        
        return $this;
    }
    
    /**
     * Execute an action on the controller.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        return parent::callAction($method, $parameters);
    }
    /**
     * The authenticated admin user
     *
     * @var \App\Models\Admin|null
     */
    protected $admin;
    
    /**
     * The breadcrumbs for the current page
     *
     * @var array
     */
    protected $breadcrumbs = [];
    
    /**
     * The current page identifier
     *
     * @var string
     */
    protected $currentPage = '';
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->admin = Auth::guard('admin')->user();
            view()->share('admin', $this->admin);
            view()->share('currentPage', $this->currentPage);
            
            // Set default breadcrumbs
            $this->breadcrumbs = [
                'dashboard' => [
                    'title' => 'Dashboard',
                    'url' => route('admin.dashboard'),
                ],
            ];
            
            view()->share('breadcrumbs', $this->breadcrumbs);
            
            return $next($request);
        });
    }
    
    /**
     * Authorize a given action for the user.
     *
     * @param  mixed  $ability
     * @param  array|mixed  $arguments
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        if (empty($this->admin)) {
            abort(403, 'Unauthorized action.');
        }
        
        if (! $this->admin->can($ability, $arguments)) {
            abort(403, 'This action is unauthorized.');
        }
        
        return true;
    }
    
    /**
     * Set the breadcrumbs for the view
     *
     * @param array $breadcrumbs
     * @return void
     */
    protected function setBreadcrumbs(array $breadcrumbs)
    {
        $this->breadcrumbs = array_merge($this->breadcrumbs, $breadcrumbs);
        view()->share('breadcrumbs', $this->breadcrumbs);
    }
    
    /**
     * Get the admin guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }
}
