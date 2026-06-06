<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterMail;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function create()
    {
        $count = Subscriber::count();
        return view('admin.newsletter.create', compact('count'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'body'    => ['required', 'string'],
        ]);

        $subscribers = Subscriber::all();
        if ($subscribers->isEmpty()) {
            return back()->with('error', 'No subscribers to send to.')->withInput();
        }

        $bodyHtml = Str::markdown($data['body']);
        $sent = 0;

        foreach ($subscribers as $sub) {
            if (! $sub->token) {
                $sub->update(['token' => Str::random(40)]);
            }
            $url = url('/unsubscribe/' . $sub->token);
            Mail::to($sub->email)->send(new NewsletterMail($data['subject'], $bodyHtml, $url));
            $sent++;
        }

        return back()->with('ok', "Newsletter sent to {$sent} subscriber(s).");
    }
}