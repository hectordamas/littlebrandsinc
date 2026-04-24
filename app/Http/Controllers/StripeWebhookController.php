<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\EnrollmentBillingProfile;
use App\Models\EnrollmentInstallment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = (string) $request->getContent();
        $sigHeader = (string) $request->header('Stripe-Signature');
        $webhookSecret = (string) config('services.stripe.webhook_secret');

        if ($webhookSecret === '') {
            return response()->json(['message' => 'Webhook secret not configured.'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            return response()->json(['message' => 'Invalid Stripe signature.'], 400);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['message' => 'Invalid payload.'], 400);
        }

        if ($event->type === 'invoice.paid') {
            $this->handleInvoicePaid($event->data->object);
        }

        if ($event->type === 'invoice.payment_failed') {
            $this->handleInvoicePaymentFailed($event->data->object);
        }

        if ($event->type === 'customer.subscription.deleted') {
            $this->handleSubscriptionDeleted($event->data->object);
        }

        return response()->json(['received' => true]);
    }

    protected function handleInvoicePaid(object $invoice): void
    {
        if (empty($invoice->subscription)) {
            return;
        }

        $profile = EnrollmentBillingProfile::query()
            ->where('stripe_subscription_id', $invoice->subscription)
            ->first();

        if (! $profile || ! $profile->enrollment) {
            return;
        }

        $periodStart = null;
        if (! empty($invoice->lines?->data) && isset($invoice->lines->data[0]->period->start)) {
            $periodStart = Carbon::createFromTimestamp((int) $invoice->lines->data[0]->period->start);
        }

        $installment = $periodStart
            ? EnrollmentInstallment::query()
                ->where('enrollment_id', $profile->enrollment_id)
                ->where('period_year', (int) $periodStart->year)
                ->where('period_month', (int) $periodStart->month)
                ->first()
            : null;

        if (! $installment) {
            $installment = EnrollmentInstallment::query()
                ->where('enrollment_id', $profile->enrollment_id)
                ->whereIn('status', ['pending', 'overdue', 'failed'])
                ->orderBy('due_date')
                ->first();
        }

        if (! $installment) {
            return;
        }

        DB::transaction(function () use ($invoice, $profile, $installment): void {
            $installment->update([
                'status' => 'paid',
                'stripe_invoice_id' => $invoice->id ?? null,
                'stripe_payment_intent_id' => $invoice->payment_intent ?? null,
                'paid_at' => now(),
            ]);

            $enrollment = $profile->enrollment()->with(['student', 'course'])->first();
            if (! $enrollment || ! $enrollment->course) {
                return;
            }

            $stripeAccount = Account::firstOrCreate(
                ['slug' => 'stripe'],
                [
                    'name' => 'Stripe',
                    'type' => 'stripe',
                    'currency' => 'USD',
                    'active' => true,
                    'meta' => ['provider' => 'stripe'],
                ]
            );

            Transaction::create([
                'enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'course_id' => $enrollment->course_id,
                'branch_id' => $enrollment->course->branch_id,
                'account_id' => $stripeAccount->id,
                'account_receivable_id' => $installment->account_receivable_id,
                'amount' => ((float) ($invoice->amount_paid ?? 0)) / 100,
                'currency' => strtoupper((string) ($invoice->currency ?? 'usd')),
                'type' => 'income',
                'status' => 'completed',
                'payment_method' => 'stripe_subscription',
                'reference' => $invoice->payment_intent ?? $invoice->id,
                'description' => 'Cobro mensual automático de suscripción.',
            ]);

            if ($installment->receivable) {
                $this->refreshReceivableBalance($installment->receivable->fresh());
            }

            $profile->update([
                'status' => 'active',
                'next_billing_date' => ! empty($invoice->period_end)
                    ? Carbon::createFromTimestamp((int) $invoice->period_end)->toDateString()
                    : $profile->next_billing_date,
            ]);
        });
    }

    protected function handleInvoicePaymentFailed(object $invoice): void
    {
        if (empty($invoice->subscription)) {
            return;
        }

        $profile = EnrollmentBillingProfile::query()
            ->where('stripe_subscription_id', $invoice->subscription)
            ->first();

        if (! $profile) {
            return;
        }

        $installment = EnrollmentInstallment::query()
            ->where('enrollment_id', $profile->enrollment_id)
            ->whereIn('status', ['pending', 'overdue', 'failed'])
            ->orderBy('due_date')
            ->first();

        if ($installment) {
            $installment->update([
                'status' => 'failed',
                'stripe_invoice_id' => $invoice->id ?? null,
                'stripe_payment_intent_id' => $invoice->payment_intent ?? null,
                'retry_count' => (int) $installment->retry_count + 1,
            ]);
        }

        $profile->update([
            'status' => 'past_due',
        ]);
    }

    protected function handleSubscriptionDeleted(object $subscription): void
    {
        if (empty($subscription->id)) {
            return;
        }

        EnrollmentBillingProfile::query()
            ->where('stripe_subscription_id', $subscription->id)
            ->update([
                'status' => 'cancelled',
                'auto_pay_enabled' => false,
            ]);
    }

    protected function refreshReceivableBalance($receivable): void
    {
        $paidAmount = (float) $receivable->transactions()->sum('amount');
        $balance = max(0, (float) $receivable->amount_total - $paidAmount);

        $status = 'pending';
        if ($balance <= 0) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        }

        $receivable->update([
            'balance_due' => $balance,
            'status' => $status,
        ]);
    }
}
