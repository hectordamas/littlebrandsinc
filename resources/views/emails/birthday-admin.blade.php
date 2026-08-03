<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva solicitud de cumpleaños - Little Brands Inc</title>
</head>
<body style="margin:0;padding:24px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;background-color:#f4f7fa;color:#1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.05);border:1px solid #e2e8f0;">
        <!-- Header -->
        <tr>
            <td bgcolor="#f97316" style="background-color:#f97316;background-image:linear-gradient(135deg, #f97316 0%, #ec4899 100%);padding:28px 32px;text-align:left;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="vertical-align:middle;">
                            <img src="{{ isset($message) && method_exists($message, 'embed') && file_exists(public_path('landing_page/logos/logo-littlebrandsinc.png')) ? $message->embed(public_path('landing_page/logos/logo-littlebrandsinc.png')) : asset('landing_page/logos/logo-littlebrandsinc.png') }}" alt="Little Brands Inc" style="max-height:54px;width:auto;background:#ffffff;padding:6px 12px;border-radius:10px;display:block;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:16px;">
                            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;letter-spacing:-0.5px;">🎂 Nueva Solicitud de Cumpleaños</h1>
                            <p style="margin:4px 0 0;color:#ffffff;font-size:14px;opacity:0.95;">Solicitud enviada desde la web</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <!-- Content -->
        <tr>
            <td style="padding:32px;">
                <p style="margin:0 0 20px;font-size:15px;line-height:1.5;color:#334155;">
                    Se ha recibido una nueva solicitud para la organización de un cumpleaños. A continuación los detalles del evento:
                </p>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;border-spacing:0;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;padding:8px 16px;">
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;width:160px;font-size:14px;">Representante:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0f172a;font-weight:700;font-size:14px;">{{ $payload['representative_name'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;font-size:14px;">Edad a celebrar:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0f172a;font-weight:700;font-size:14px;">🎉 {{ $payload['age_to_celebrate'] ?? 'N/A' }} años</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;font-size:14px;">Fecha del evento:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#f97316;font-weight:700;font-size:14px;">📅 {{ $payload['event_date'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;font-size:14px;">Hora tentativa:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0f172a;font-size:14px;">⏰ {{ $payload['start_time'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;font-size:14px;">Ubicación:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0f172a;font-size:14px;">
                            @php
                                $locs = [
                                    'sede_san_luis' => 'Sede San Luis',
                                    'sede_los_campitos' => 'Sede Los Campitos',
                                    'sede_los_chorros' => 'Sede Los Chorros',
                                    'other' => 'Otra ubicación'
                                ];
                                $locName = $locs[$payload['location_type'] ?? ''] ?? ($payload['location_type'] ?? 'N/A');
                            @endphp
                            {{ $locName }}
                            @if(!empty($payload['event_location']))
                                ({{ $payload['event_location'] }})
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;font-size:14px;">Programa de interés:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0ea5e9;font-weight:700;font-size:14px;">
                            {{ strtoupper($payload['program_interest'] ?? 'N/A') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;font-size:14px;">Estimado de niños:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0f172a;font-size:14px;">{{ $payload['estimated_children'] ?? 'N/A' }} niños</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;font-size:14px;">Rango edad invitados:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0f172a;font-size:14px;">{{ $payload['guest_age_range'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;font-size:14px;">Correo electrónico:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0f172a;font-size:14px;"><a href="mailto:{{ $payload['email'] ?? '' }}" style="color:#f97316;text-decoration:none;font-weight:600;">{{ $payload['email'] ?? 'N/A' }}</a></td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;font-weight:600;color:#475569;font-size:14px;">Teléfono:</td>
                        <td style="padding:10px 0;color:#0f172a;font-size:14px;"><a href="tel:{{ $payload['phone'] ?? '' }}" style="color:#f97316;text-decoration:none;font-weight:600;">{{ $payload['phone'] ?? 'N/A' }}</a></td>
                    </tr>
                </table>

                @if(!empty($payload['additional_services']) && is_array($payload['additional_services']))
                <div style="margin-top:20px;">
                    <p style="margin:0 0 8px;font-size:14px;font-weight:600;color:#475569;">Servicios Adicionales de Interés:</p>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        @foreach($payload['additional_services'] as $service)
                            <span style="display:inline-block;background:#ffedd5;color:#c2410c;font-size:13px;font-weight:600;padding:4px 12px;border-radius:20px;margin-right:6px;margin-bottom:6px;">{{ $service }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($payload['comments']))
                <div style="margin-top:20px;">
                    <p style="margin:0 0 8px;font-size:14px;font-weight:600;color:#475569;">Comentarios Adicionales:</p>
                    <div style="padding:16px;background:#fff7ed;border-radius:10px;border-left:4px solid #f97316;color:#334155;font-size:14px;line-height:1.6;white-space:pre-line;">{{ $payload['comments'] }}</div>
                </div>
                @endif
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td style="background:#f8fafc;padding:16px 32px;text-align:center;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;">
                Little Brands Inc &bull; Notificación de solicitudes de cumpleaños
            </td>
        </tr>
    </table>
</body>
</html>
