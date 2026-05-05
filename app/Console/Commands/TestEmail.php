<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('📧 Testing Email Configuration...');
        $this->newLine();
        
        // Display current configuration
        $this->info('📋 Current Mail Configuration:');
        $this->line('   MAIL_MAILER: ' . config('mail.default'));
        $this->line('   MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        $this->line('   MAIL_PORT: ' . config('mail.mailers.smtp.port'));
        $this->line('   MAIL_USERNAME: ' . config('mail.mailers.smtp.username'));
        $this->line('   MAIL_ENCRYPTION: ' . (config('mail.mailers.smtp.encryption') ?? 'null'));
        $this->line('   MAIL_FROM_ADDRESS: ' . config('mail.from.address'));
        $this->line('   MAIL_FROM_NAME: ' . config('mail.from.name'));
        $this->newLine();
        
        // Check if configuration looks valid
        if (empty(config('mail.mailers.smtp.host'))) {
            $this->error('❌ MAIL_HOST is not configured!');
            return 1;
        }
        
        if (empty(config('mail.mailers.smtp.username'))) {
            $this->error('❌ MAIL_USERNAME is not configured!');
            return 1;
        }
        
        if (empty(config('mail.mailers.smtp.password'))) {
            $this->error('❌ MAIL_PASSWORD is not configured!');
            return 1;
        }
        
        $this->info('✅ Configuration looks valid');
        $this->newLine();
        
        // Try to send test email
        $this->info("📤 Sending test email to: {$email}");
        
        try {
            Mail::raw('This is a test email from CHIBO BRAND. If you receive this, your email configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                        ->subject('CHIBO BRAND - Email Configuration Test');
                
                if (config('mail.from.address')) {
                    $message->from(config('mail.from.address'), config('mail.from.name') ?? 'CHIBO BRAND');
                }
            });
            
            $this->info('✅ Email sent successfully!');
            $this->line('   Please check your inbox (and spam folder) for the test email.');
            
            Log::info('Test email sent successfully', [
                'to' => $email,
                'from' => config('mail.from.address')
            ]);
            
            return 0;
            
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            $this->error('❌ Failed to send email: Transport Exception');
            $this->error('   Error: ' . $e->getMessage());
            $this->newLine();
            $this->warn('💡 Common issues:');
            $this->line('   • Check if SMTP credentials are correct');
            $this->line('   • Verify MAIL_ENCRYPTION matches your server (ssl/tls)');
            $this->line('   • Check if port 465 (SSL) or 587 (TLS) is open');
            $this->line('   • Ensure your email account allows SMTP access');
            $this->line('   • For Hostinger: Make sure SMTP is enabled in your hosting panel');
            
            Log::error('Test email failed: Transport Exception', [
                'to' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . get_class($e));
            $this->error('   Error: ' . $e->getMessage());
            
            Log::error('Test email failed', [
                'to' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}








