@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Cuentas</title>
@endsection

@section('content')
    <div class="modal fade" id="createAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="createAccountForm" action="{{ route('accounts.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Cuenta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_account_name" class="form-label">Nombre</label>
                        <input
                            id="create_account_name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-control"
                            maxlength="255"
                            required>
                        <small id="create_account_error" class="text-danger d-none"></small>
                    </div>
                    <small class="text-muted">La cuenta se crea activa, en moneda USD y tipo "other" por defecto.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="createAccountSubmit" class="btn btn-primary">Guardar Cuenta</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editAccountForm" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar nombre de cuenta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_account_id">
                    <div class="mb-3">
                        <label for="edit_account_name" class="form-label">Nombre</label>
                        <input
                            id="edit_account_name"
                            type="text"
                            name="name"
                            value=""
                            class="form-control"
                            maxlength="255"
                            required>
                        <small id="edit_account_error" class="text-danger d-none"></small>
                    </div>
                    <small class="text-muted">Solo se modificará el nombre.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="editAccountSubmit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Cuentas</h5>
                            <span class="text-muted">Registra cuentas y actualiza solo su nombre cuando sea necesario.</span>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                            <i class="fas fa-plus"></i> Agregar cuenta
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Moneda</th>
                                        <th>Estado</th>
                                        <th style="width: 120px;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="accountsTableBody">
                                    @forelse ($accounts as $account)
                                        <tr data-account-id="{{ $account->id }}">
                                            <td>{{ $account->id }}</td>
                                            <td data-field="name">{{ $account->name }}</td>
                                            <td data-field="currency">{{ strtoupper($account->currency) }}</td>
                                            <td>
                                                <span data-field="active" class="badge {{ $account->active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $account->active ? 'Activa' : 'Inactiva' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-success js-edit-account"
                                                    data-account-id="{{ $account->id }}"
                                                    data-account-name="{{ $account->name }}"
                                                    data-edit-url="{{ route('accounts.update', $account->id) }}">
                                                    Editar nombre
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="accountsEmptyRow">
                                            <td colspan="5" class="text-center text-muted">No hay cuentas registradas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const createForm = document.getElementById('createAccountForm');
            const editForm = document.getElementById('editAccountForm');
            const createModalEl = document.getElementById('createAccountModal');
            const editModalEl = document.getElementById('editAccountModal');
            const createModal = new bootstrap.Modal(createModalEl);
            const editModal = new bootstrap.Modal(editModalEl);
            const tableBody = document.getElementById('accountsTableBody');
            const createError = document.getElementById('create_account_error');
            const editError = document.getElementById('edit_account_error');
            const createSubmit = document.getElementById('createAccountSubmit');
            const editSubmit = document.getElementById('editAccountSubmit');
            const editNameInput = document.getElementById('edit_account_name');
            const editIdInput = document.getElementById('edit_account_id');

            function showError(el, message) {
                el.textContent = message || 'Error de validacion.';
                el.classList.remove('d-none');
            }

            function hideError(el) {
                el.textContent = '';
                el.classList.add('d-none');
            }

            function buildRow(account) {
                const activeClass = account.active ? 'bg-success' : 'bg-secondary';
                return `
                    <tr data-account-id="${account.id}">
                        <td>${account.id}</td>
                        <td data-field="name">${account.name}</td>
                        <td data-field="currency">${account.currency}</td>
                        <td><span data-field="active" class="badge ${activeClass}">${account.active_label}</span></td>
                        <td>
                            <button
                                type="button"
                                class="btn btn-sm btn-success js-edit-account"
                                data-account-id="${account.id}"
                                data-account-name="${account.name}"
                                data-edit-url="/accounts/${account.id}">
                                Editar nombre
                            </button>
                        </td>
                    </tr>
                `;
            }

            function removeEmptyRow() {
                const emptyRow = document.getElementById('accountsEmptyRow');
                if (emptyRow) {
                    emptyRow.remove();
                }
            }

            async function request(url, method, formData) {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                let data = {};
                try {
                    data = await response.json();
                } catch (e) {
                    data = {};
                }

                if (!response.ok) {
                    const message = data?.errors?.name?.[0] || data?.message || 'No fue posible completar la operacion.';
                    throw new Error(message);
                }

                return data;
            }

            createForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                hideError(createError);
                createSubmit.disabled = true;

                try {
                    const payload = new FormData(createForm);
                    const data = await request(createForm.action, 'POST', payload);
                    removeEmptyRow();
                    tableBody.insertAdjacentHTML('afterbegin', buildRow(data.account));
                    createForm.reset();
                    createModal.hide();
                    Swal.fire({
                        icon: 'success',
                        text: data.message || 'Cuenta creada exitosamente.',
                        confirmButtonColor: '#198754'
                    });
                } catch (error) {
                    showError(createError, error.message);
                } finally {
                    createSubmit.disabled = false;
                }
            });

            tableBody.addEventListener('click', function(event) {
                const button = event.target.closest('.js-edit-account');
                if (!button) {
                    return;
                }

                hideError(editError);
                editIdInput.value = button.dataset.accountId;
                editNameInput.value = button.dataset.accountName || '';
                editForm.action = button.dataset.editUrl;
                editModal.show();
            });

            editForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                hideError(editError);
                editSubmit.disabled = true;

                try {
                    const payload = new FormData(editForm);
                    payload.set('_method', 'PUT');

                    const data = await request(editForm.action, 'POST', payload);
                    const row = tableBody.querySelector(`tr[data-account-id="${data.account.id}"]`);

                    if (row) {
                        row.querySelector('[data-field="name"]').textContent = data.account.name;
                        row.querySelector('[data-field="currency"]').textContent = data.account.currency;

                        const activeBadge = row.querySelector('[data-field="active"]');
                        activeBadge.textContent = data.account.active_label;
                        activeBadge.className = 'badge ' + (data.account.active ? 'bg-success' : 'bg-secondary');

                        const editBtn = row.querySelector('.js-edit-account');
                        editBtn.dataset.accountName = data.account.name;
                    }

                    editModal.hide();
                    Swal.fire({
                        icon: 'success',
                        text: data.message || 'Cuenta actualizada exitosamente.',
                        confirmButtonColor: '#198754'
                    });
                } catch (error) {
                    showError(editError, error.message);
                } finally {
                    editSubmit.disabled = false;
                }
            });
        });
    </script>
@endsection
