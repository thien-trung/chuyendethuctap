<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Newsletter\Facades\Newsletter as FacadesNewsletter;
use Spatie\Newsletter\NewsletterFacade as Newsletter;
use Spatie\Newsletter\Support\NewsletterList;

class NewsLetterController extends Controller
{
    public function store(Request $request)
    {
        $email = $request->input('value');

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email format.'], 400);
        }

        try {
            // Check if already subscribed
            if (!Newsletter::isSubscribed($email)) {
                Newsletter::subscribePending($email);
                return 'Thanks for Subscribing! Check your email for next steps!';
            } else {
                return 'Sorry, you have already subscribed!';
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to subscribe. Please try again later.'], 500);
        }
    }
}