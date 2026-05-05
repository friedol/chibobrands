<?php
/**
 * Storage Symlink Setup for Shared Hosting
 * 
 * This script helps set up the storage symlink for Laravel applications
 * on shared hosting environments where you don't have SSH access.
 * 
 * Instructions:
 * 1. Upload this file to your public_html directory
 * 2. Access it via browser: https://yourdomain.com/setup-storage.php
 * 3. Follow the on-screen instructions
 * 4. DELETE this file after successful setup for security
 */

// Security check - only allow this to run in specific conditions
$allowed_ips = ['127.0.0.1', '::1']; // Add your IP if needed
$current_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Uncomment the line below to restrict access to specific IPs
// if (!in_array($current_ip, $allowed_ips)) {
//     die('Access denied. This script can only be run from authorized IPs.');
// }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Storage Setup - Shared Hosting</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Laravel Storage Setup for Shared Hosting</h1>
        
        <?php
        $action = $_GET['action'] ?? 'check';
        
        if ($action === 'check') {
            echo '<div class="info"><strong>Step 1:</strong> Checking current storage setup...</div>';
            
            // Check if storage directory exists
            $storage_path = __DIR__ . '/../storage/app/public';
            $public_storage = __DIR__ . '/storage';
            
            echo '<h3>Current Status:</h3>';
            echo '<ul>';
            echo '<li>Storage directory exists: ' . (is_dir($storage_path) ? '✅ Yes' : '❌ No') . '</li>';
            echo '<li>Public storage symlink exists: ' . (is_link($public_storage) ? '✅ Yes' : '❌ No') . '</li>';
            echo '<li>Storage directory writable: ' . (is_writable($storage_path) ? '✅ Yes' : '❌ No') . '</li>';
            echo '<li>Public directory writable: ' . (is_writable(__DIR__) ? '✅ Yes' : '❌ No') . '</li>';
            echo '</ul>';
            
            if (is_link($public_storage)) {
                echo '<div class="success"><strong>✅ Storage symlink already exists!</strong><br>';
                echo 'Target: ' . readlink($public_storage) . '</div>';
                echo '<a href="?action=test" class="btn">Test Storage Access</a>';
            } else {
                echo '<div class="warning"><strong>⚠️ Storage symlink not found!</strong><br>';
                echo 'You need to create the symlink manually.</div>';
                echo '<a href="?action=create" class="btn">Try to Create Symlink</a>';
                echo '<a href="?action=manual" class="btn">Manual Instructions</a>';
            }
        }
        
        elseif ($action === 'create') {
            echo '<div class="info"><strong>Step 2:</strong> Attempting to create storage symlink...</div>';
            
            $storage_path = realpath(__DIR__ . '/../storage/app/public');
            $public_storage = __DIR__ . '/storage';
            
            if (!$storage_path) {
                echo '<div class="error"><strong>❌ Error:</strong> Storage directory not found at: ' . __DIR__ . '/../storage/app/public</div>';
            } else {
                // Remove existing file/link if it exists
                if (file_exists($public_storage)) {
                    if (is_link($public_storage)) {
                        unlink($public_storage);
                    } else {
                        echo '<div class="warning">Removing existing file/directory at storage location...</div>';
                        if (is_dir($public_storage)) {
                            rmdir($public_storage);
                        } else {
                            unlink($public_storage);
                        }
                    }
                }
                
                // Try to create symlink
                if (symlink($storage_path, $public_storage)) {
                    echo '<div class="success"><strong>✅ Success!</strong> Storage symlink created successfully.</div>';
                    echo '<p>Symlink created: <code>' . $public_storage . '</code> → <code>' . $storage_path . '</code></p>';
                    echo '<a href="?action=test" class="btn">Test Storage Access</a>';
                } else {
                    echo '<div class="error"><strong>❌ Failed to create symlink!</strong><br>';
                    echo 'This might be due to server restrictions. Try the manual method below.</div>';
                    echo '<a href="?action=manual" class="btn">Manual Instructions</a>';
                }
            }
        }
        
        elseif ($action === 'test') {
            echo '<div class="info"><strong>Step 3:</strong> Testing storage access...</div>';
            
            $test_file = 'storage/test-' . time() . '.txt';
            $test_content = 'Storage test file created at ' . date('Y-m-d H:i:s');
            
            if (file_put_contents($test_file, $test_content)) {
                echo '<div class="success"><strong>✅ Storage is working!</strong></div>';
                echo '<p>Test file created: <a href="' . $test_file . '" target="_blank">' . $test_file . '</a></p>';
                
                // Clean up test file
                unlink($test_file);
                echo '<p><small>Test file cleaned up.</small></p>';
                
                echo '<div class="info"><strong>🎉 Setup Complete!</strong><br>';
                echo 'Your Laravel storage is now properly configured. You can delete this setup file for security.</div>';
                echo '<a href="?action=cleanup" class="btn btn-danger">Delete Setup File</a>';
            } else {
                echo '<div class="error"><strong>❌ Storage test failed!</strong><br>';
                echo 'Cannot write to storage directory. Check permissions.</div>';
            }
        }
        
        elseif ($action === 'manual') {
            echo '<h3>📋 Manual Setup Instructions</h3>';
            echo '<div class="step">';
            echo '<h4>Method 1: Using cPanel File Manager</h4>';
            echo '<ol>';
            echo '<li>Login to your cPanel</li>';
            echo '<li>Open File Manager</li>';
            echo '<li>Navigate to your public_html directory</li>';
            echo '<li>Look for the "storage" folder in public_html</li>';
            echo '<li>If it exists, delete it (it should be a symlink, not a real folder)</li>';
            echo '<li>Right-click in the public_html directory</li>';
            echo '<li>Select "Create Symbolic Link" or "Create Link"</li>';
            echo '<li>Set the link name as: <code>storage</code></li>';
            echo '<li>Set the target path as: <code>../storage/app/public</code></li>';
            echo '<li>Click Create</li>';
            echo '</ol>';
            echo '</div>';
            
            echo '<div class="step">';
            echo '<h4>Method 2: Using FTP/SFTP</h4>';
            echo '<ol>';
            echo '<li>Connect to your server via FTP/SFTP</li>';
            echo '<li>Navigate to public_html directory</li>';
            echo '<li>Delete any existing "storage" folder</li>';
            echo '<li>Create a symbolic link from <code>public_html/storage</code> to <code>../storage/app/public</code></li>';
            echo '<li>Most FTP clients have a "Create Symlink" option in the right-click menu</li>';
            echo '</ol>';
            echo '</div>';
            
            echo '<div class="step">';
            echo '<h4>Method 3: Using .htaccess (Alternative)</h4>';
            echo '<p>If symlinks are not supported, you can use URL rewriting:</p>';
            echo '<div class="code">';
            echo '# Add this to your public_html/.htaccess file<br>';
            echo 'RewriteEngine On<br>';
            echo 'RewriteRule ^storage/(.*)$ ../storage/app/public/$1 [L]';
            echo '</div>';
            echo '<p><strong>Note:</strong> This method is less efficient than symlinks but works when symlinks are disabled.</p>';
            echo '</div>';
            
            echo '<a href="?action=check" class="btn">Check Status Again</a>';
        }
        
        elseif ($action === 'cleanup') {
            echo '<div class="info"><strong>Cleaning up setup file...</strong></div>';
            
            if (unlink(__FILE__)) {
                echo '<div class="success"><strong>✅ Setup file deleted successfully!</strong></div>';
                echo '<p>Your Laravel storage is now properly configured and secure.</p>';
                echo '<p><a href="/">← Back to your website</a></p>';
            } else {
                echo '<div class="error"><strong>❌ Could not delete setup file!</strong><br>';
                echo 'Please manually delete the setup-storage.php file for security.</div>';
            }
        }
        ?>
        
        <hr>
        <div class="info">
            <strong>ℹ️ Important Notes:</strong>
            <ul>
                <li>This script should be deleted after successful setup for security</li>
                <li>If symlinks are not supported by your hosting provider, use the .htaccess method</li>
                <li>Make sure your storage/app/public directory has proper permissions (755 or 777)</li>
                <li>Test your file uploads after setup to ensure everything works correctly</li>
            </ul>
        </div>
    </div>
</body>
</html>





