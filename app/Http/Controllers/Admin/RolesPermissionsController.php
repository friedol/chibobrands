<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class RolesPermissionsController extends Controller
{
    /**
     * Get default roles and permissions structure.
     */
    private function getDefaultRolesPermissions(): array
    {
        return [
            'super_admin' => [
                'name' => 'Super Admin',
                'description' => 'Full access to all features and settings. Can manage all users and system configurations.',
                'editable' => false,
                'permissions' => [
                    // Administration
                    'manage_users'          => true,
                    'manage_roles'          => true,
                    'manage_settings'       => true,
                    'reset_passwords'       => true,
                    'view_audit_logs'       => true,
                    'manage_departments'    => true,
                    // Sales & Operations
                    'manage_orders'         => true,
                    'delete_all_orders'     => true,
                    'manage_pos'            => true,
                    'manage_customers'      => true,
                    'manage_leads'          => true,
                    'manage_design_tasks'   => true,
                    'view_contact_messages' => true,
                    // Inventory & Products
                    'manage_products'       => true,
                    'manage_inventory'      => true,
                    'manage_hero_slides'    => true,
                    // Finance & Reporting
                    'manage_finance'        => true,
                    'manage_reports'        => true,
                    'manage_performance'    => true,
                    // Modules
                    'manage_hr'             => true,
                    'manage_marketing'      => true,
                    'manage_delivery'       => true,
                    'manage_gatekeeper'     => true,
                ],
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'Can manage most settings and content. Cannot manage other admin users or system settings.',
                'permissions' => [
                    'manage_users'          => true,
                    'manage_roles'          => false,
                    'manage_settings'       => true,
                    'reset_passwords'       => true,
                    'view_audit_logs'       => true,
                    'manage_departments'    => true,
                    'manage_orders'         => true,
                    'delete_all_orders'     => false,
                    'manage_pos'            => true,
                    'manage_customers'      => true,
                    'manage_leads'          => true,
                    'manage_design_tasks'   => true,
                    'view_contact_messages' => true,
                    'manage_products'       => true,
                    'manage_inventory'      => true,
                    'manage_hero_slides'    => true,
                    'manage_finance'        => true,
                    'manage_reports'        => true,
                    'manage_performance'    => true,
                    'manage_hr'             => true,
                    'manage_marketing'      => true,
                    'manage_delivery'       => true,
                    'manage_gatekeeper'     => true,
                ],
            ],
            'manager' => [
                'name' => 'Manager',
                'description' => 'Can manage products, categories, and view reports. Limited access to system settings.',
                'permissions' => [
                    'manage_users'          => false,
                    'manage_roles'          => false,
                    'manage_settings'       => false,
                    'reset_passwords'       => false,
                    'view_audit_logs'       => true,
                    'manage_departments'    => true,
                    'manage_orders'         => true,
                    'delete_all_orders'     => false,
                    'manage_pos'            => true,
                    'manage_customers'      => true,
                    'manage_leads'          => true,
                    'manage_design_tasks'   => true,
                    'view_contact_messages' => true,
                    'manage_products'       => true,
                    'manage_inventory'      => true,
                    'manage_hero_slides'    => true,
                    'manage_finance'        => true,
                    'manage_reports'        => true,
                    'manage_performance'    => true,
                    'manage_hr'             => true,
                    'manage_marketing'      => true,
                    'manage_delivery'       => false,
                    'manage_gatekeeper'     => false,
                ],
            ],
            'receptionist' => [
                'name' => 'Receptionist',
                'description' => 'Can manage customer interactions, create design tasks, and assign tasks to designers.',
                'permissions' => [
                    'manage_users'          => false,
                    'manage_roles'          => false,
                    'manage_settings'       => false,
                    'reset_passwords'       => false,
                    'view_audit_logs'       => false,
                    'manage_departments'    => false,
                    'manage_orders'         => true,
                    'delete_all_orders'     => false,
                    'manage_pos'            => true,
                    'manage_customers'      => true,
                    'manage_leads'          => true,
                    'manage_design_tasks'   => true,
                    'view_contact_messages' => true,
                    'manage_products'       => false,
                    'manage_inventory'      => false,
                    'manage_hero_slides'    => false,
                    'manage_finance'        => false,
                    'manage_reports'        => false,
                    'manage_performance'    => false,
                    'manage_hr'             => false,
                    'manage_marketing'      => false,
                    'manage_delivery'       => false,
                    'manage_gatekeeper'     => false,
                ],
            ],
            'designer' => [
                'name' => 'Designer',
                'description' => 'Can view and update assigned design tasks. Limited access to other features.',
                'permissions' => [
                    'manage_users'          => false,
                    'manage_roles'          => false,
                    'manage_settings'       => false,
                    'reset_passwords'       => false,
                    'view_audit_logs'       => false,
                    'manage_departments'    => false,
                    'manage_orders'         => false,
                    'delete_all_orders'     => false,
                    'manage_pos'            => false,
                    'manage_customers'      => false,
                    'manage_leads'          => false,
                    'manage_design_tasks'   => true,
                    'view_contact_messages' => true,
                    'manage_products'       => false,
                    'manage_inventory'      => false,
                    'manage_hero_slides'    => false,
                    'manage_finance'        => false,
                    'manage_reports'        => false,
                    'manage_performance'    => false,
                    'manage_hr'             => false,
                    'manage_marketing'      => false,
                    'manage_delivery'       => false,
                    'manage_gatekeeper'     => false,
                ],
            ],
            'operator' => [
                'name' => 'Operator',
                'description' => 'Can act as both designer and receptionist. Can perform all tasks assigned to both roles.',
                'permissions' => [
                    'manage_users'          => false,
                    'manage_roles'          => false,
                    'manage_settings'       => false,
                    'reset_passwords'       => false,
                    'view_audit_logs'       => false,
                    'manage_departments'    => false,
                    'manage_orders'         => true,
                    'delete_all_orders'     => false,
                    'manage_pos'            => true,
                    'manage_customers'      => true,
                    'manage_leads'          => true,
                    'manage_design_tasks'   => true,
                    'view_contact_messages' => true,
                    'manage_products'       => false,
                    'manage_inventory'      => false,
                    'manage_hero_slides'    => false,
                    'manage_finance'        => false,
                    'manage_reports'        => false,
                    'manage_performance'    => false,
                    'manage_hr'             => false,
                    'manage_marketing'      => false,
                    'manage_delivery'       => false,
                    'manage_gatekeeper'     => false,
                ],
            ],
            'saler' => [
                'name' => 'Sales',
                'description' => 'Can manage sales, customers, and orders. Limited access to system settings.',
                'permissions' => [
                    'manage_users'          => false,
                    'manage_roles'          => false,
                    'manage_settings'       => false,
                    'reset_passwords'       => false,
                    'view_audit_logs'       => false,
                    'manage_departments'    => false,
                    'manage_orders'         => true,
                    'delete_all_orders'     => false,
                    'manage_pos'            => true,
                    'manage_customers'      => true,
                    'manage_leads'          => true,
                    'manage_design_tasks'   => false,
                    'view_contact_messages' => false,
                    'manage_products'       => false,
                    'manage_inventory'      => false,
                    'manage_hero_slides'    => false,
                    'manage_finance'        => false,
                    'manage_reports'        => false,
                    'manage_performance'    => true,
                    'manage_hr'             => false,
                    'manage_marketing'      => false,
                    'manage_delivery'       => false,
                    'manage_gatekeeper'     => false,
                ],
            ],
            'accountant' => [
                'name' => 'Accountant',
                'description' => 'Full access to financial dashboards, expenses, and cash flow management.',
                'permissions' => [
                    'manage_users'          => false,
                    'manage_roles'          => false,
                    'manage_settings'       => false,
                    'reset_passwords'       => false,
                    'view_audit_logs'       => true,
                    'manage_departments'    => true,
                    'manage_orders'         => true,
                    'delete_all_orders'     => false,
                    'manage_pos'            => false,
                    'manage_customers'      => true,
                    'manage_leads'          => true,
                    'manage_design_tasks'   => false,
                    'view_contact_messages' => true,
                    'manage_products'       => false,
                    'manage_inventory'      => false,
                    'manage_hero_slides'    => false,
                    'manage_finance'        => true,
                    'manage_reports'        => true,
                    'manage_performance'    => true,
                    'manage_hr'             => true,
                    'manage_marketing'      => false,
                    'manage_delivery'       => false,
                    'manage_gatekeeper'     => true,
                ],
            ],
            'marketing_manager' => [
                'name' => 'Marketing Manager',
                'description' => 'Manages marketing campaigns, ads, product penetration, events, and reporting.',
                'permissions' => [
                    'manage_users'          => false,
                    'manage_roles'          => false,
                    'manage_settings'       => false,
                    'reset_passwords'       => false,
                    'view_audit_logs'       => false,
                    'manage_departments'    => false,
                    'manage_orders'         => false,
                    'delete_all_orders'     => false,
                    'manage_pos'            => false,
                    'manage_customers'      => true,
                    'manage_leads'          => true,
                    'manage_design_tasks'   => false,
                    'view_contact_messages' => true,
                    'manage_products'       => true,
                    'manage_inventory'      => false,
                    'manage_hero_slides'    => true,
                    'manage_finance'        => false,
                    'manage_reports'        => true,
                    'manage_performance'    => true,
                    'manage_hr'             => false,
                    'manage_marketing'      => true,
                    'manage_delivery'       => false,
                    'manage_gatekeeper'     => false,
                ],
            ],
            'hr_officer' => [
                'name' => 'HR Officer',
                'description' => 'Manages employee records, attendance, leave requests, and KPI evaluations.',
                'permissions' => [
                    'manage_users'          => true,
                    'manage_roles'          => false,
                    'manage_settings'       => false,
                    'reset_passwords'       => false,
                    'view_audit_logs'       => true,
                    'manage_departments'    => true,
                    'manage_orders'         => false,
                    'delete_all_orders'     => false,
                    'manage_pos'            => false,
                    'manage_customers'      => false,
                    'manage_leads'          => false,
                    'manage_design_tasks'   => false,
                    'view_contact_messages' => false,
                    'manage_products'       => false,
                    'manage_inventory'      => false,
                    'manage_hero_slides'    => false,
                    'manage_finance'        => false,
                    'manage_reports'        => true,
                    'manage_performance'    => true,
                    'manage_hr'             => true,
                    'manage_marketing'      => false,
                    'manage_delivery'       => false,
                    'manage_gatekeeper'     => false,
                ],
            ],
            'delivery' => [
                'name' => 'Delivery',
                'description' => 'Can view assigned delivery tasks and update delivery status.',
                'permissions' => [
                    'manage_users'          => false,
                    'manage_roles'          => false,
                    'manage_settings'       => false,
                    'reset_passwords'       => false,
                    'view_audit_logs'       => false,
                    'manage_departments'    => false,
                    'manage_orders'         => false,
                    'delete_all_orders'     => false,
                    'manage_pos'            => false,
                    'manage_customers'      => false,
                    'manage_leads'          => false,
                    'manage_design_tasks'   => false,
                    'view_contact_messages' => false,
                    'manage_products'       => false,
                    'manage_inventory'      => false,
                    'manage_hero_slides'    => false,
                    'manage_finance'        => false,
                    'manage_reports'        => false,
                    'manage_performance'    => false,
                    'manage_hr'             => false,
                    'manage_marketing'      => false,
                    'manage_delivery'       => true,
                    'manage_gatekeeper'     => true,
                ],
            ],
            'gatekeeper' => [
                'name' => 'Gatekeeper',
                'description' => 'Can verify and track items leaving and entering the premises.',
                'permissions' => [
                    'manage_users'          => false,
                    'manage_roles'          => false,
                    'manage_settings'       => false,
                    'reset_passwords'       => false,
                    'view_audit_logs'       => false,
                    'manage_departments'    => false,
                    'manage_orders'         => false,
                    'delete_all_orders'     => false,
                    'manage_pos'            => false,
                    'manage_customers'      => false,
                    'manage_leads'          => false,
                    'manage_design_tasks'   => false,
                    'view_contact_messages' => false,
                    'manage_products'       => false,
                    'manage_inventory'      => true,
                    'manage_hero_slides'    => false,
                    'manage_finance'        => false,
                    'manage_reports'        => false,
                    'manage_performance'    => false,
                    'manage_hr'             => false,
                    'manage_marketing'      => false,
                    'manage_delivery'       => false,
                    'manage_gatekeeper'     => true,
                ],
            ],
        ];
    }

    /**
     * Get roles and permissions (from cache or default).
     */
    private function getRolesPermissions(): array
    {
        $default = $this->getDefaultRolesPermissions();
        $cached = Cache::get('roles_permissions', $default);
        
        // Merge cached with default to ensure all keys exist
        foreach ($default as $role => $data) {
            if (!isset($cached[$role])) {
                $cached[$role] = $data;
            } else {
                // Ensure all permissions exist
                foreach ($data['permissions'] as $perm => $value) {
                    if (!isset($cached[$role]['permissions'][$perm])) {
                        $cached[$role]['permissions'][$perm] = $value;
                    }
                }
            }
        }
        
        return $cached;
    }

    /**
     * Display roles and permissions configuration page.
     */
    public function index(): View
    {
        // Only super_admin can access this page
        $user = Auth::user();
        if ($user->role !== 'super_admin') {
            abort(403, 'Unauthorized access. Only super administrators can view roles and permissions.');
        }

        $rolesPermissions = $this->getRolesPermissions();

        // Get user counts per role
        $roleCounts = [];
        foreach (array_keys($rolesPermissions) as $role) {
            $roleCounts[$role] = User::where('role', $role)->count();
        }

        return view('admin.roles-permissions.index', [
            'rolesPermissions' => $rolesPermissions,
            'roleCounts' => $roleCounts,
        ]);
    }

    /**
     * Update permissions for a role.
     */
    public function update(Request $request, string $role): RedirectResponse
    {
        // Only super_admin can update permissions
        $user = Auth::user();
        if ($user->role !== 'super_admin') {
            abort(403, 'Unauthorized access. Only super administrators can edit roles and permissions.');
        }

        $defaultPermissions = $this->getDefaultRolesPermissions();
        
        if (!isset($defaultPermissions[$role])) {
            return back()->with('error', 'Invalid role specified.');
        }

        // Get current permissions
        $rolesPermissions = $this->getRolesPermissions();
        
        // Get submitted permissions
        $submittedPermissions = $request->input('permissions', []);
        
        // Prevent editing super_admin permissions
        if ($role === 'super_admin') {
            return back()->with('error', 'Super Admin permissions cannot be modified.');
        }

        // Validate and update permissions
        $roleData = $rolesPermissions[$role];
        
        // Update each permission from the request
        foreach ($roleData['permissions'] as $permission => $currentValue) {
            // Update permission value from request (only if permission exists in default)
            if (isset($defaultPermissions[$role]['permissions'][$permission])) {
                $roleData['permissions'][$permission] = isset($submittedPermissions[$permission]) && $submittedPermissions[$permission] === '1';
            }
        }
        
        // Update the role's permissions (preserve name, description, etc.)
        $rolesPermissions[$role]['permissions'] = $roleData['permissions'];
        
        // Store in cache (persists in file cache driver)
        Cache::forever('roles_permissions', $rolesPermissions);

        return back()->with('success', 'Permissions updated successfully for ' . $roleData['name'] . '.');
    }
}






