<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::query()
            ->with(['program', 'branch'])
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('created_at')
            ->get();

        return view('messages.index', [
            'messages' => $messages,
            'unreadCount' => (int) $messages->whereNull('read_at')->count(),
        ]);
    }

    public function markAsRead(Request $request, ContactMessage $message): RedirectResponse
    {
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        if ($request->expectsJson()) {
            return redirect()->back();
        }

        return back()->with('success', 'Mensaje marcado como leido.');
    }

    public function markAsUnread(ContactMessage $message): RedirectResponse
    {
        $message->update(['read_at' => null]);

        return back()->with('success', 'Mensaje marcado como no leido.');
    }
}
