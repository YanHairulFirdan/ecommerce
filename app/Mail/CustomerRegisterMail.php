<?php

namespace App\Mail;

use App\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerRegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $customer;
    protected $randomPassword;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Customer $customer, $randomPassword)
    {
        $this->customer = $customer;
        $this->randomPassword = $randomPassword;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Verifikasi pendaftaran anda')
            ->view('view.name')
            ->with([
                'customer' => $this->customer,
                'randomPassword' => $this->randomPassword
            ]);
    }
}
