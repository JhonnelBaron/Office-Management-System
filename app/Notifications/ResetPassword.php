<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class ResetPassword extends BaseResetPassword
{
    public function toMail($notifiable)
    {
        // Construct the frontend URL, pointing to your frontend route (e.g., /update-password)
        $frontendUrl = 'http://localhost:3000/update-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);
    
        // Pass this frontend URL to the email template
        return (new MailMessage)
            ->subject('Reset Your Password')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $frontendUrl)  // The action button should link to your frontend
            ->line('If you did not request a password reset, no further action is required.')
            ->with([
                'actionUrl' => $frontendUrl,  // Ensure actionUrl is the correct frontend URL
                'displayableActionUrl' => $frontendUrl,  // Make sure this is also the correct URL for the subcopy
            ]);
    }
}
