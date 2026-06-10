<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
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

    public function markAsRead(Request $request, ContactMessage $message): RedirectResponse|JsonResponse
    {
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'read_at' => optional($message->read_at)->toDateTimeString(),
            ]);
        }

        return back()->with('success', 'Mensaje marcado como leido.');
    }

    public function markAsUnread(Request $request, ContactMessage $message): RedirectResponse|JsonResponse
    {
        $message->update(['read_at' => null]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'read_at' => null,
            ]);
        }

        return back()->with('success', 'Mensaje marcado como no leido.');
    }
}
