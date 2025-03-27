<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mailgun\Mailgun;

class MailController extends Controller
{
    public function subscribe(Request $request, Mailgun $mgClient)
    {
        # Add a subscriber to the mailing list
        $mailing_list = env('MAILGUN_LIST', '');
        $address = $request->get('email');
        [$name] = explode('@', $address);

        try {
            $result = $mgClient->mailingList()->member()->create(
                $mailing_list,
                $address,
                $name
            );
        } catch (\Exception $e) {
            Log::error($e->getCode() . ' ' . $e->getMessage());
            return (new \Statamic\View\View)
                ->template('errors.subscribe')
                ->layout('mail');
        }

        return (new \Statamic\View\View)
            ->template('mail.subscribe')
            ->layout('mail')
            ->with(['result' => $result]);
    }
}
