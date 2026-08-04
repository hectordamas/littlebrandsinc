<?php

use App\Models\{Account, AccountPayable, AccountReceivable, Branch, Transaction, User};

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'Administrador']);

    $this->branch = Branch::create([
        'name' => 'SEDE SAN LUIS',
        'address' => 'San Luis',
        'email' => 'sanluis@test.com',
        'phone' => '+58 424-0000001',
        'active' => true,
    ]);

    $this->account = Account::create([
        'name' => 'Caja Chica USD',
        'slug' => 'caja-chica-usd',
        'currency' => 'USD',
        'active' => true,
    ]);
});

test('se pueden registrar cxp y cxc sin sede (gastos e ingresos generales)', function () {
    $this->actingAs($this->admin);

    // Crear CxP General
    $responsePayable = $this->post(route('finance.payables.store'), [
        'branch_id' => '',
        'vendor_name' => 'Proveedor General S.A.',
        'title' => 'Mantenimiento de Oficina Central',
        'amount_total' => 300.00,
    ]);

    $responsePayable->assertRedirect(route('finance.payables'));

    $payable = AccountPayable::where('title', 'Mantenimiento de Oficina Central')->first();
    expect($payable)->not->toBeNull();
    expect($payable->branch_id)->toBeNull();

    // Crear CxC General
    $responseCollection = $this->post(route('finance.collections.store'), [
        'branch_id' => '',
        'title' => 'Patrocinio Evento Anual',
        'amount_total' => 1500.00,
    ]);

    $responseCollection->assertRedirect(route('finance.collections'));

    $receivable = AccountReceivable::where('title', 'Patrocinio Evento Anual')->first();
    expect($receivable)->not->toBeNull();
    expect($receivable->branch_id)->toBeNull();
});

test('se puede registrar un movimiento manual sin sede y se suma en el resumen general', function () {
    $this->actingAs($this->admin);

    // Registrar ingreso con sede
    Transaction::create([
        'branch_id' => $this->branch->id,
        'account_id' => $this->account->id,
        'amount' => 500.00,
        'currency' => 'USD',
        'type' => 'income',
        'status' => 'completed',
    ]);

    // Registrar ingreso general sin sede
    $responseTransaction = $this->post(route('finance.transactions.store'), [
        'branch_id' => '',
        'account_id' => $this->account->id,
        'amount' => 200.00,
        'type' => 'income',
        'status' => 'completed',
        'description' => 'Donación General',
    ]);

    $responseTransaction->assertRedirect(route('finance.index'));

    $generalTransaction = Transaction::where('description', 'Donación General')->first();
    expect($generalTransaction)->not->toBeNull();
    expect($generalTransaction->branch_id)->toBeNull();

    // Consultar dashboard general (sin filtro branch_id)
    $responseIndex = $this->getJson(route('finance.index', ['format' => 'json']));
    $responseIndex->assertOk();

    // Verificar que ingresos completados sume 500 + 200 = 700
    $data = $responseIndex->json();
    expect($data['summary']['completedIncome'])->toEqual(700);

    // Consultar filtrando por "general"
    $responseGeneral = $this->getJson(route('finance.index', ['branch_id' => 'general', 'format' => 'json']));
    $responseGeneral->assertOk();
    $dataGeneral = $responseGeneral->json();

    // Solo el ingreso general (200)
    expect($dataGeneral['summary']['completedIncome'])->toEqual(200);
    expect(count($dataGeneral['transactions']))->toBe(1);
    expect($dataGeneral['transactions'][0]['branch'])->toBe('Ingresos Generales');
});

test('se puede editar y eliminar un movimiento financiero', function () {
    $this->actingAs($this->admin);

    $transaction = Transaction::create([
        'branch_id' => $this->branch->id,
        'account_id' => $this->account->id,
        'amount' => 150.00,
        'currency' => 'USD',
        'type' => 'income',
        'status' => 'completed',
        'description' => 'Pago inicial',
    ]);

    // Editar el movimiento
    $responseUpdate = $this->put(route('finance.transactions.update', $transaction), [
        'amount' => 250.00,
        'account_id' => $this->account->id,
        'type' => 'expense',
        'status' => 'completed',
        'description' => 'Pago corregido a gasto',
    ]);

    $responseUpdate->assertRedirect();

    $transaction->refresh();
    expect((float) $transaction->amount)->toBe(250.00);
    expect($transaction->type)->toBe('expense');
    expect($transaction->description)->toBe('Pago corregido a gasto');

    // Eliminar el movimiento
    $responseDestroy = $this->delete(route('finance.transactions.destroy', $transaction));
    $responseDestroy->assertRedirect();

    expect(Transaction::find($transaction->id))->toBeNull();
});
