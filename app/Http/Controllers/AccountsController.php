<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountsController extends Controller
{
    public function index(): View
    {
        $accounts = Account::query()
            ->orderByDesc('id')
            ->get();

        return view('accounts.index', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $account = Account::create([
            'name' => $validated['name'],
            'slug' => $this->buildUniqueSlug($validated['name']),
            'type' => 'other',
            'currency' => 'USD',
            'active' => true,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Cuenta creada exitosamente.',
                'account' => $this->serializeAccount($account),
            ]);
        }

        return redirect()->route('accounts.index')->with('success', 'Cuenta creada exitosamente.');
    }

    public function edit(int $id): View
    {
        $account = Account::query()->findOrFail($id);

        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $account = Account::query()->findOrFail($id);

        $account->name = $validated['name'];
        $account->slug = $this->buildUniqueSlug($validated['name'], $account->id);
        $account->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Cuenta actualizada exitosamente.',
                'account' => $this->serializeAccount($account),
            ]);
        }

        return redirect()->route('accounts.index')->with('success', 'Cuenta actualizada exitosamente.');
    }

    protected function serializeAccount(Account $account): array
    {
        return [
            'id' => $account->id,
            'name' => $account->name,
            'slug' => $account->slug,
            'type' => strtoupper($account->type),
            'currency' => strtoupper($account->currency),
            'active' => (bool) $account->active,
            'active_label' => $account->active ? 'Activa' : 'Inactiva',
        ];
    }

    protected function buildUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'cuenta';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Account::query()
                ->where('slug', $slug)
                ->when($ignoreId, function ($query) use ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                })
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
