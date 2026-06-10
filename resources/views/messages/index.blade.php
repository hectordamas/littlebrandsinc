@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Mensajes</title>
@endsection

@section('styles')
    <style>
        /* ── Inbox Container ── */
        .inbox-wrapper {
            display: flex;
            height: calc(100vh - 140px);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }

        /* ── Sidebar (Message List) ── */
        .inbox-sidebar {
            width: 420px;
            min-width: 340px;
            max-width: 480px;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            background: #fafbfc;
        }

        .inbox-sidebar-header {
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #f0f4ff 0%, #fafbfc 100%);
            flex-shrink: 0;
        }

        .inbox-sidebar-header h5 {
            font-weight: 700;
            font-size: 1.1rem;
            color: #0f172a;
            margin-bottom: 0.15rem;
        }

        .inbox-sidebar-header .inbox-unread-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: #64748b;
        }

        .inbox-sidebar-header .inbox-unread-badge .unread-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #3b82f6;
            display: inline-block;
        }

        .inbox-search {
            padding: 0.7rem 1rem;
            border-bottom: 1px solid #eef0f4;
            flex-shrink: 0;
        }

        .inbox-search input {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.5rem 0.75rem 0.5rem 2.2rem;
            font-size: 0.85rem;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0'/%3E%3C/svg%3E") no-repeat 0.65rem center;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            color: #334155;
        }

        .inbox-search input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        .inbox-message-list {
            flex: 1;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .inbox-message-list::-webkit-scrollbar {
            width: 5px;
        }

        .inbox-message-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .inbox-message-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* ── Message Item ── */
        .inbox-msg-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.9rem 1.15rem;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background 0.2s ease, border-left 0.2s ease;
            position: relative;
            border-left: 3px solid transparent;
        }

        .inbox-msg-item:hover {
            background: #eef2ff;
        }

        .inbox-msg-item.active {
            background: #e0e7ff;
            border-left-color: #3b82f6;
        }

        .inbox-msg-item.unread {
            background: #fff;
        }

        .inbox-msg-item.unread .inbox-msg-sender {
            font-weight: 700;
            color: #0f172a;
        }

        .inbox-msg-item.unread .inbox-msg-preview {
            font-weight: 600;
            color: #334155;
        }

        .inbox-msg-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: #fff;
            text-transform: uppercase;
            position: relative;
        }

        .inbox-msg-avatar .unread-indicator {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: #3b82f6;
            border: 2px solid #fff;
        }

        .inbox-msg-body {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .inbox-msg-top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .inbox-msg-sender {
            font-size: 0.87rem;
            font-weight: 500;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }

        .inbox-msg-time {
            font-size: 0.72rem;
            color: #94a3b8;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .inbox-msg-child {
            font-size: 0.78rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .inbox-msg-preview {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 400;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        /* ── Reading Panel ── */
        .inbox-reading-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
            min-width: 0;
        }

        .inbox-reading-empty {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            gap: 0.75rem;
        }

        .inbox-reading-empty i {
            font-size: 3.5rem;
            color: #cbd5e1;
        }

        .inbox-reading-empty p {
            font-size: 0.95rem;
            margin: 0;
        }

        .inbox-detail-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
            background: #fafbfc;
        }

        .inbox-detail-header .sender-info {
            display: flex;
            align-items: center;
            gap: 0.9rem;
        }

        .inbox-detail-header .sender-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.05rem;
            color: #fff;
        }

        .inbox-detail-header .sender-name {
            font-weight: 700;
            font-size: 1rem;
            color: #0f172a;
        }

        .inbox-detail-header .sender-meta {
            font-size: 0.78rem;
            color: #64748b;
        }

        .inbox-detail-header .detail-actions {
            display: flex;
            gap: 0.5rem;
        }

        .inbox-detail-header .detail-actions .btn {
            border-radius: 8px;
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
        }

        .inbox-detail-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .inbox-detail-body::-webkit-scrollbar {
            width: 5px;
        }

        .inbox-detail-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .inbox-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .inbox-info-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.85rem 1rem;
            transition: border-color 0.2s;
        }

        .inbox-info-item:hover {
            border-color: #bfdbfe;
        }

        .inbox-info-item .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .inbox-info-item .info-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
            word-break: break-word;
        }

        .inbox-info-item .info-value a {
            color: #3b82f6;
            text-decoration: none;
        }

        .inbox-info-item .info-value a:hover {
            text-decoration: underline;
        }

        .inbox-comment-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
        }

        .inbox-comment-section .comment-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            color: #64748b;
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .inbox-comment-section .comment-body {
            font-size: 0.95rem;
            line-height: 1.65;
            color: #1e293b;
            white-space: pre-wrap;
        }

        .inbox-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .inbox-status-badge.read {
            background: #f1f5f9;
            color: #64748b;
        }

        .inbox-status-badge.unread {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .inbox-msg-count {
            padding: 0.5rem 1.15rem;
            border-top: 1px solid #e2e8f0;
            font-size: 0.76rem;
            color: #94a3b8;
            text-align: center;
            flex-shrink: 0;
            background: #fafbfc;
        }

        /* ── Responsive ── */
        @media (max-width: 992px) {
            .inbox-wrapper {
                flex-direction: column;
                height: auto;
                min-height: 600px;
            }

            .inbox-sidebar {
                width: 100%;
                max-width: none;
                max-height: 350px;
                border-right: none;
                border-bottom: 1px solid #e2e8f0;
            }

            .inbox-reading-panel {
                min-height: 400px;
            }
        }

        /* ── Avatar color palette ── */
        .avatar-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .avatar-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
        .avatar-violet { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .avatar-pink { background: linear-gradient(135deg, #ec4899, #db2777); }
        .avatar-teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }
        .avatar-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .avatar-emerald { background: linear-gradient(135deg, #10b981, #059669); }
        .avatar-rose { background: linear-gradient(135deg, #f43f5e, #e11d48); }

        /* ── Fade-in animation ── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in-up {
            animation: fadeInUp 0.3s ease-out;
        }
    </style>
@endsection

@section('content')
    <div class="inbox-wrapper">
        {{-- ── Sidebar: Message List ── --}}
        <div class="inbox-sidebar">
            <div class="inbox-sidebar-header">
                <h5><i class="fas fa-inbox me-2" style="color: #3b82f6;"></i>Bandeja de Entrada</h5>
                <div class="inbox-unread-badge" id="unreadBadge">
                    <span class="unread-dot"></span>
                    <span id="unreadCounter">{{ $unreadCount }} sin leer</span>
                </div>
            </div>

            <div class="inbox-search">
                <input type="text" id="inboxSearch" placeholder="Buscar por nombre, email o mensaje...">
            </div>

            <div class="inbox-message-list" id="messageList">
                @forelse ($messages as $idx => $message)
                    @php
                        $avatarColors = ['avatar-blue','avatar-indigo','avatar-violet','avatar-pink','avatar-teal','avatar-amber','avatar-emerald','avatar-rose'];
                        $colorClass = $avatarColors[$message->id % count($avatarColors)];
                        $initials = collect(explode(' ', $message->representative_name))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join('');
                        $timeAgo = $message->created_at?->diffForHumans(short: true) ?? '';
                    @endphp
                    <div class="inbox-msg-item {{ $message->read_at ? '' : 'unread' }}"
                         data-id="{{ $message->id }}"
                         data-is-read="{{ $message->read_at ? '1' : '0' }}"
                         data-created-at="{{ $message->created_at?->format('d/m/Y H:i') }}"
                         data-representative-name="{{ e($message->representative_name) }}"
                         data-child-name="{{ e($message->child_name) }}"
                         data-child-age="{{ $message->child_age }}"
                         data-program-name="{{ e(optional($message->program)->name ?? 'N/A') }}"
                         data-branch-name="{{ e(optional($message->branch)->name ?? 'N/A') }}"
                         data-phone="{{ e($message->phone) }}"
                         data-email="{{ e($message->email) }}"
                         data-comment="{{ e($message->comment) }}"
                         data-color="{{ $colorClass }}"
                         data-initials="{{ $initials }}">

                        <div class="inbox-msg-avatar {{ $colorClass }}">
                            {{ $initials }}
                            @unless($message->read_at)
                                <span class="unread-indicator"></span>
                            @endunless
                        </div>

                        <div class="inbox-msg-body">
                            <div class="inbox-msg-top-row">
                                <span class="inbox-msg-sender">{{ $message->representative_name }}</span>
                                <span class="inbox-msg-time">{{ $timeAgo }}</span>
                            </div>
                            <span class="inbox-msg-child">
                                <i class="fas fa-child" style="font-size: 0.68rem; margin-right: 2px;"></i>
                                {{ $message->child_name }} ({{ $message->child_age }} años)
                            </span>
                            <span class="inbox-msg-preview">{{ Str::limit($message->comment, 70) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="inbox-reading-empty" style="padding: 3rem;">
                        <i class="far fa-envelope-open"></i>
                        <p>No hay mensajes registrados</p>
                    </div>
                @endforelse
            </div>

            <div class="inbox-msg-count" id="messageCount">
                {{ $messages->count() }} mensaje(s) en total
            </div>
        </div>

        {{-- ── Reading Panel ── --}}
        <div class="inbox-reading-panel">
            {{-- Empty state --}}
            <div class="inbox-reading-empty" id="readingEmpty">
                <i class="far fa-envelope-open"></i>
                <p>Selecciona un mensaje para ver su contenido</p>
                <small style="color: #cbd5e1;">Los mensajes no leídos aparecen resaltados en la lista</small>
            </div>

            {{-- Detail view (hidden by default) --}}
            <div id="readingDetail" class="d-none" style="display: flex; flex-direction: column; height: 100%;">
                <div class="inbox-detail-header">
                    <div class="sender-info">
                        <div class="sender-avatar" id="detailAvatar"></div>
                        <div>
                            <div class="sender-name" id="detailSenderName"></div>
                            <div class="sender-meta" id="detailSenderMeta"></div>
                        </div>
                    </div>
                    <div class="detail-actions">
                        <span class="inbox-status-badge" id="detailStatusBadge"></span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleReadBtn" title="Cambiar estado">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                        @if($messages->isNotEmpty())
                            <a href="#" id="whatsappLink" target="_blank" class="btn btn-sm btn-success" title="Contactar por WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="inbox-detail-body fade-in-up">
                    <div class="inbox-info-grid">
                        <div class="inbox-info-item">
                            <div class="info-label"><i class="fas fa-user me-1"></i> Representante</div>
                            <div class="info-value" id="detailRepresentative"></div>
                        </div>
                        <div class="inbox-info-item">
                            <div class="info-label"><i class="fas fa-child me-1"></i> Niño/a</div>
                            <div class="info-value" id="detailChild"></div>
                        </div>
                        <div class="inbox-info-item">
                            <div class="info-label"><i class="fas fa-graduation-cap me-1"></i> Programa</div>
                            <div class="info-value" id="detailProgram"></div>
                        </div>
                        <div class="inbox-info-item">
                            <div class="info-label"><i class="fas fa-building me-1"></i> Sede</div>
                            <div class="info-value" id="detailBranch"></div>
                        </div>
                        <div class="inbox-info-item">
                            <div class="info-label"><i class="fas fa-phone me-1"></i> Teléfono</div>
                            <div class="info-value" id="detailPhone"></div>
                        </div>
                        <div class="inbox-info-item">
                            <div class="info-label"><i class="fas fa-envelope me-1"></i> Email</div>
                            <div class="info-value" id="detailEmail"></div>
                        </div>
                    </div>

                    <div class="inbox-comment-section">
                        <div class="comment-header">
                            <i class="fas fa-comment-dots"></i>
                            Mensaje
                        </div>
                        <div class="comment-body" id="detailComment"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const csrfToken = '{{ csrf_token() }}';
            const baseUrl = '{{ url("mensajes") }}';
            let unreadCount = Number(@json($unreadCount));
            let activeMessageId = null;

            function updateUnreadBadge() {
                const $badge = $('#unreadCounter');
                $badge.text(unreadCount + ' sin leer');

                if (unreadCount <= 0) {
                    $('#unreadBadge .unread-dot').hide();
                } else {
                    $('#unreadBadge .unread-dot').show();
                }
            }

            function selectMessage($item) {
                const data = {
                    id: Number($item.data('id')),
                    is_read: $item.data('is-read') === 1 || $item.data('is-read') === '1',
                    created_at: $item.data('created-at') || 'N/A',
                    representative_name: $item.data('representative-name') || 'N/A',
                    child_name: $item.data('child-name') || 'N/A',
                    child_age: $item.data('child-age') || 'N/A',
                    program_name: $item.data('program-name') || 'N/A',
                    branch_name: $item.data('branch-name') || 'N/A',
                    phone: $item.data('phone') || 'N/A',
                    email: $item.data('email') || 'N/A',
                    comment: $item.data('comment') || 'Sin comentario',
                    color: $item.data('color') || 'avatar-blue',
                    initials: $item.data('initials') || '?',
                };

                // Highlight active
                $('.inbox-msg-item').removeClass('active');
                $item.addClass('active');
                activeMessageId = data.id;

                // Fill reading panel
                $('#readingEmpty').addClass('d-none');
                $('#readingDetail').removeClass('d-none').css('display', 'flex');

                // Re-trigger animation
                const $body = $('#readingDetail .inbox-detail-body');
                $body.removeClass('fade-in-up');
                void $body[0].offsetWidth; // Force reflow
                $body.addClass('fade-in-up');

                // Avatar
                $('#detailAvatar')
                    .attr('class', 'sender-avatar ' + data.color)
                    .text(data.initials);

                // Header
                $('#detailSenderName').text(data.representative_name);
                $('#detailSenderMeta').text(data.created_at + '  ·  ' + data.child_name + ' (' + data.child_age + ' años)');

                // Status badge
                updateStatusBadge(data.is_read);

                // Info cards
                $('#detailRepresentative').text(data.representative_name);
                $('#detailChild').text(data.child_name + ' (' + data.child_age + ' años)');
                $('#detailProgram').text(data.program_name);
                $('#detailBranch').text(data.branch_name);

                // Phone with WhatsApp link
                if (data.phone && data.phone !== 'N/A') {
                    $('#detailPhone').html('<a href="tel:' + data.phone + '">' + data.phone + '</a>');
                    const cleanPhone = data.phone.replace(/[^0-9+]/g, '');
                    $('#whatsappLink').attr('href', 'https://wa.me/' + cleanPhone).show();
                } else {
                    $('#detailPhone').text('N/A');
                    $('#whatsappLink').hide();
                }

                // Email with mailto
                if (data.email && data.email !== 'N/A') {
                    $('#detailEmail').html('<a href="mailto:' + data.email + '">' + data.email + '</a>');
                } else {
                    $('#detailEmail').text('N/A');
                }

                // Comment
                $('#detailComment').text(data.comment);

                // Auto-mark as read
                if (!data.is_read) {
                    markAsRead(data.id, $item);
                }
            }

            function updateStatusBadge(isRead) {
                const $badge = $('#detailStatusBadge');
                if (isRead) {
                    $badge.attr('class', 'inbox-status-badge read')
                          .html('<i class="fas fa-check-double"></i> Leído');
                    $('#toggleReadBtn').html('<i class="fas fa-eye-slash"></i>').attr('title', 'Marcar como no leído');
                } else {
                    $badge.attr('class', 'inbox-status-badge unread')
                          .html('<i class="fas fa-circle" style="font-size: 6px;"></i> No leído');
                    $('#toggleReadBtn').html('<i class="fas fa-eye"></i>').attr('title', 'Marcar como leído');
                }
            }

            async function markAsRead(messageId, $item) {
                try {
                    const response = await fetch(`${baseUrl}/${messageId}/read`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        $item.removeClass('unread');
                        $item.data('is-read', '1');
                        $item.find('.unread-indicator').remove();

                        if (unreadCount > 0) unreadCount--;
                        updateUnreadBadge();
                        updateStatusBadge(true);
                    }
                } catch (error) {
                    console.error('Error marking as read:', error);
                }
            }

            async function markAsUnread(messageId, $item) {
                try {
                    const response = await fetch(`${baseUrl}/${messageId}/unread`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        $item.addClass('unread');
                        $item.data('is-read', '0');

                        // Add unread indicator back
                        if (!$item.find('.unread-indicator').length) {
                            $item.find('.inbox-msg-avatar').append('<span class="unread-indicator"></span>');
                        }

                        unreadCount++;
                        updateUnreadBadge();
                        updateStatusBadge(false);
                    }
                } catch (error) {
                    console.error('Error marking as unread:', error);
                }
            }

            // Click on message
            $('#messageList').on('click', '.inbox-msg-item', function() {
                selectMessage($(this));
            });

            // Toggle read/unread
            $('#toggleReadBtn').on('click', async function() {
                if (!activeMessageId) return;
                const $item = $(`.inbox-msg-item[data-id="${activeMessageId}"]`);
                const isRead = $item.data('is-read') === 1 || $item.data('is-read') === '1';

                if (isRead) {
                    await markAsUnread(activeMessageId, $item);
                } else {
                    await markAsRead(activeMessageId, $item);
                }
            });

            // Search
            $('#inboxSearch').on('input', function() {
                const query = $(this).val().toLowerCase().trim();
                let visibleCount = 0;

                $('.inbox-msg-item').each(function() {
                    const name = ($(this).data('representative-name') || '').toLowerCase();
                    const child = ($(this).data('child-name') || '').toLowerCase();
                    const email = ($(this).data('email') || '').toLowerCase();
                    const comment = ($(this).data('comment') || '').toLowerCase();

                    const matches = !query || name.includes(query) || child.includes(query) || email.includes(query) || comment.includes(query);
                    $(this).toggle(matches);
                    if (matches) visibleCount++;
                });

                $('#messageCount').text(visibleCount + ' mensaje(s) encontrado(s)');
            });

            // Auto-select first if available
            const $firstUnread = $('.inbox-msg-item.unread').first();
            if ($firstUnread.length) {
                selectMessage($firstUnread);
            }
        });
    </script>
@endsection
