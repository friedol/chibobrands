<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

class AdminController extends BaseController
{
    /**
     * The current page identifier
     *
     * @var string
     */
    protected $currentPage = 'admins';
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->middleware('admin.role:super_admin,admin')->except(['profile', 'updateProfile']);
        
        // Set breadcrumbs
        $this->breadcrumbs['admins'] = [
            'title' => 'Admin Users',
            'url' => route('admin.admins.index'),
        ];
        
        view()->share('breadcrumbs', $this->breadcrumbs);
        view()->share('currentPage', $this->currentPage);
    }
    
    public function index(Request $request)
    {
        $this->authorize('viewAny', Admin::class);
        
        $query = Admin::query();
        
        // Filter by role if specified
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }
        
        $admins = $query->latest()->paginate(15);
        
        // Get available roles for filter
        $roles = $this->getAvailableRoles();
        
        return view('admin.admins.index', [
            'admins' => $admins,
            'roles' => $roles,
            'breadcrumbs' => $this->breadcrumbs,
            'currentPage' => 'admins',
        ]);
    }
    
    public function create()
    {
        $this->authorize('create', Admin::class);
        
        return view('admin.admins.create', [
            'breadcrumbs' => array_merge($this->breadcrumbs, [
                'create' => [
                    'title' => 'Create Admin',
                    'url' => route('admin.admins.create'),
                ]
            ]),
            'currentPage' => 'admins',
            'roles' => $this->getAvailableRoles(),
        ]);
    }
    
    public function store(Request $request)
    {
        $this->authorize('create', Admin::class);
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(array_keys($this->getAvailableRoles()))],
            'is_active' => 'sometimes|boolean',
        ]);
        
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->has('is_active');
        
        $admin = Admin::create($data);
        
        // Send welcome email
        // Mail::to($admin->email)->send(new AdminWelcomeMail($admin, $data['password']));
        
        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin user created successfully.');
    }
    
    public function edit(Admin $admin)
    {
        $this->authorize('update', $admin);
        
        return view('admin.admins.edit', [
            'admin' => $admin,
            'breadcrumbs' => array_merge($this->breadcrumbs, [
                'edit' => [
                    'title' => 'Edit Admin: ' . $admin->name,
                    'url' => route('admin.admins.edit', $admin->id),
                ]
            ]),
            'currentPage' => 'admins',
            'roles' => $this->getAvailableRoles(),
        ]);
    }
    
    public function update(Request $request, Admin $admin)
    {
        $this->authorize('update', $admin);
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'role' => ['required', 'string', Rule::in(array_keys($this->getAvailableRoles()))],
            'is_active' => 'sometimes|boolean',
        ];
        
        // Only require password if it's being updated
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }
        
        $data = $request->validate($rules);
        
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        
        $data['is_active'] = $request->has('is_active');
        
        $admin->update($data);
        
        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin user updated successfully.');
    }
    
    public function destroy(Admin $admin)
    {
        $this->authorize('delete', $admin);
        
        // Prevent deleting yourself
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        
        $admin->delete();
        
        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin user deleted successfully.');
    }
    
    public function profile()
    {
        $admin = auth('admin')->user();
        
        return view('admin.profile', [
            'admin' => $admin,
            'breadcrumbs' => [
                'dashboard' => [
                    'title' => 'Dashboard',
                    'url' => route('admin.dashboard'),
                ],
                'profile' => [
                    'title' => 'My Profile',
                    'url' => route('admin.profile'),
                ],
            ],
            'currentPage' => 'profile',
        ]);
    }
    
    public function updateProfile(Request $request)
    {
        $admin = auth('admin')->user();
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'current_password' => ['required_with:new_password', 'current_password:admin'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);
        
        // Update password if provided
        if (!empty($data['new_password'])) {
            $admin->password = Hash::make($data['new_password']);
        }
        
        $admin->name = $data['name'];
        $admin->email = $data['email'];
        $admin->save();
        
        return back()->with('success', 'Profile updated successfully.');
    }
    
    /**
     * Get the available roles for the admin user
     * 
     * @return array
     */
    protected function getAvailableRoles(): array
    {
        $roles = [
            'admin' => 'Admin',
            'receptionist' => 'Receptionist',
            'designer' => 'Designer',
            'saler' => 'Sales',
            'saler' => 'Sales',
            'operator' => 'Operator',
            'delivery' => 'Delivery',
            'gatekeeper' => 'Gatekeeper',
        ];
        
        // Only super admins can create other super admins
        if ($this->admin && $this->admin->role === 'super_admin') {
            $roles = ['super_admin' => 'Super Admin'] + $roles;
        }
        
        return $roles;
    }
    
    /**
     * Get the description for a role
     * 
     * @param string $role
     * @return string
     */
    protected function getRoleDescription(string $role): string
    {
        $descriptions = [
            'super_admin' => 'Full access to all features and settings. Can manage all users and system configurations.',
            'admin' => 'Can manage most settings and content. Cannot manage other admin users or system settings.',
            'receptionist' => 'Can manage customer interactions, appointments, and assign tasks to designers.',
            'designer' => 'Can view and update assigned design tasks. Limited access to other features.',
            'saler' => 'Can manage sales, customers, and orders. Limited access to system settings.',
            'saler' => 'Can manage sales, customers, and orders. Limited access to system settings.',
            'operator' => 'Can act as both designer and receptionist. Can perform all tasks assigned to both roles.',
            'delivery' => 'Can view assigned delivery tasks and update delivery status.',
            'gatekeeper' => 'Can verify items leaving the premises.',
        ];
        
        return $descriptions[$role] ?? 'No description available for this role.';
    }
}
