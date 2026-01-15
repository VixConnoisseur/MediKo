<?php
/**
 * Role-Based Access Control (RBAC)
 * 
 * Handles permissions and role-based access control for the application.
 */
class RBAC {
    private $db;
    private $auth;
    
    // Define available roles and their permissions
    private $roles = [
        'admin' => [
            'name' => 'Administrator',
            'permissions' => [
                'view_dashboard',
                'manage_users',
                'manage_medications',
                'view_reports',
                'system_settings',
                'manage_roles',
                'view_analytics',
                'export_data',
                'manage_emergency_contacts',
                'view_all_health_logs',
                'manage_notifications'
            ]
        ],
        'user' => [
            'name' => 'Regular User',
            'permissions' => [
                'view_dashboard',
                'manage_own_medications',
                'view_own_reports',
                'manage_own_profile',
                'manage_own_emergency_contacts',
                'manage_own_health_logs',
                'manage_own_notifications'
            ]
        ]
    ];
    
    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }
    
    /**
     * Check if current user has a specific permission
     */
    public function hasPermission($permission) {
        $user = $this->auth->getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        // Admin has all permissions
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Check if the role exists and has the permission
        return isset($this->roles[$user['role']]) && 
               in_array($permission, $this->roles[$user['role']]['permissions']);
    }
    
    /**
     * Middleware to check if user has required permission
     */
    public function requirePermission($permission, $redirect = '/') {
        if (!$this->hasPermission($permission)) {
            if (!headers_sent()) {
                header("Location: {$redirect}");
            }
            exit('Access Denied: You do not have permission to access this page.');
        }
        return true;
    }
    
    /**
     * Middleware to check if user has any of the required permissions
     */
    public function requireAnyPermission($permissions, $redirect = '/') {
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }
        
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        
        if (!headers_sent()) {
            header("Location: {$redirect}");
        }
        exit('Access Denied: You do not have permission to access this page.');
    }
    
    /**
     * Get all available roles
     */
    public function getRoles() {
        return array_keys($this->roles);
    }
    
    /**
     * Get role name by role key
     */
    public function getRoleName($role) {
        return $this->roles[$role]['name'] ?? $role;
    }
    
    /**
     * Get all permissions for a role
     */
    public function getRolePermissions($role) {
        return $this->roles[$role]['permissions'] ?? [];
    }
    
    /**
     * Check if a role exists
     */
    public function roleExists($role) {
        return isset($this->roles[$role]);
    }
    
    /**
     * Get all available permissions
     */
    public function getAllPermissions() {
        $permissions = [];
        foreach ($this->roles as $role) {
            $permissions = array_merge($permissions, $role['permissions']);
        }
        return array_unique($permissions);
    }
    
    /**
     * Get current user's role
     */
    public function getCurrentUserRole() {
        $user = $this->auth->getCurrentUser();
        return $user ? $user['role'] : 'guest';
    }
    
    /**
     * Check if current user has a specific role
     */
    public function hasRole($role) {
        $user = $this->auth->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Check if current user has any of the specified roles
     */
    public function hasAnyRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        $user = $this->auth->getCurrentUser();
        return $user && in_array($user['role'], $roles);
    }
    
    /**
     * Middleware to restrict access to specific roles
     */
    public function requireRole($roles, $redirect = '/') {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        $user = $this->auth->getCurrentUser();
        
        if (!$user || !in_array($user['role'], $roles)) {
            if (!headers_sent()) {
                header("Location: {$redirect}");
            }
            exit('Access Denied: You do not have permission to access this page.');
        }
        
        return true;
    }
}

// Initialize RBAC
$rbac = new RBAC($db, $auth);
