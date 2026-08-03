<?php

namespace App\Http\Controllers;

use App\Mail\BirthdayInquiryAdminMailable;
use App\Mail\BirthdayInquiryConfirmationMailable;
use App\Models\BirthdayInquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BirthdayInquiryController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'representative_name' => 'required|string|max:160',
            'phone' => 'required|string|max:40',
            'email' => 'required|email|max:180',
            'age_to_celebrate' => 'required|integer|min:1|max:100',
            'event_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|string|max:20',
            'location_type' => 'required|string|in:sede_san_luis,sede_los_campitos,sede_los_chorros,other',
            'event_location' => 'nullable|string|max:255',
            'estimated_children' => 'required|integer|min:1',
            'guest_age_range' => 'required|string|max:100',
            'program_interest' => 'required|string|in:strikers,paddlers',
            'additional_services' => 'nullable|array',
            'comments' => 'nullable|string|max:1000',
        ]);

        // 1. Persist inquiry in database FIRST to guarantee application inbox delivery
        $inquiry = BirthdayInquiry::create($validated);

        // 2. Queue emails to Admin and User in background with exception handling
        try {
            $recipientAddress = (string) config('mail.admin_recipient.address');
            $recipientName = (string) config('mail.admin_recipient.name', 'Little Brands Inc');

            // Send notification to Admin if MAIL_TO_ADDRESS is configured
            if (!empty($recipientAddress)) {
                Mail::to($recipientAddress, $recipientName)->queue(new BirthdayInquiryAdminMailable($validated));
            } else {
                Log::warning('Birthday inquiry email for admin skipped: MAIL_TO_ADDRESS is empty.');
            }

            // Send confirmation receipt to User
            if (!empty($validated['email'])) {
                Mail::to($validated['email'], $validated['representative_name'])->queue(new BirthdayInquiryConfirmationMailable($validated));
            }
        } catch (\Throwable $exception) {
            Log::error('Birthday inquiry background email dispatch failed', [
                'birthday_inquiry_id' => $inquiry->id,
                'error' => $exception->getMessage(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '¡Tu solicitud de cumpleaños ha sido enviada con éxito! Nuestro equipo se pondrá en contacto contigo muy pronto.',
                'inquiry' => $inquiry
            ]);
        }

        return back()->with('success', '¡Tu solicitud de cumpleaños ha sido enviada con éxito! Te contactaremos pronto.');
    }

    public function index()
    {
        $inquiries = BirthdayInquiry::query()
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('created_at')
            ->get();

        return view('birthdays.index', [
            'inquiries' => $inquiries,
            'unreadCount' => (int) $inquiries->whereNull('read_at')->count(),
        ]);
    }

    public function markAsRead(Request $request, BirthdayInquiry $birthday): JsonResponse|RedirectResponse
    {
        if (!$birthday->read_at) {
            $birthday->update(['read_at' => now()]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'read_at' => optional($birthday->read_at)->toDateTimeString(),
            ]);
        }

        return back()->with('success', 'Solicitud marcada como leída.');
    }

    public function markAsUnread(Request $request, BirthdayInquiry $birthday): JsonResponse|RedirectResponse
    {
        $birthday->update(['read_at' => null]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'read_at' => null,
            ]);
        }

        return back()->with('success', 'Solicitud marcada como no leída.');
    }
}
