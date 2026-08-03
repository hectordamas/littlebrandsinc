<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Solicitud de cumpleaños recibida! - Little Brands Inc</title>
</head>
<body style="margin:0;padding:24px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;background-color:#f4f7fa;color:#1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.05);border:1px solid #e2e8f0;">
        <!-- Header -->
        <tr>
            <td bgcolor="#f97316" style="background-color:#f97316;background-image:linear-gradient(135deg, #f97316 0%, #ec4899 100%);padding:32px;text-align:center;">
                <img src="{{ isset($message) && method_exists($message, 'embed') && file_exists(public_path('landing_page/logos/logo-littlebrandsinc.png')) ? $message->embed(public_path('landing_page/logos/logo-littlebrandsinc.png')) : asset('landing_page/logos/logo-littlebrandsinc.png') }}" alt="Little Brands Inc" style="max-height:60px;width:auto;background:#ffffff;padding:8px 16px;border-radius:12px;display:inline-block;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                <h1 style="margin:20px 0 0;color:#ffffff;font-size:24px;font-weight:700;letter-spacing:-0.5px;">¡Gracias por contactarnos! 🎈</h1>
                <p style="margin:6px 0 0;color:#ffffff;font-size:15px;opacity:0.95;">Hemos recibido tu solicitud para celebrar un cumpleaños inolvidable</p>
            </td>
        </tr>
        <!-- Content -->
        <tr>
            <td style="padding:32px;">
                <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#1e293b;">
                    Hola <strong>{{ $payload['representative_name'] ?? 'estimado/a representante' }}</strong>,
                </p>
                <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#475569;">
                    ¡Nos alegra mucho que pienses en nosotros para celebrar este momento tan especial! Nuestro equipo está revisando la disponibilidad y detalles de tu solicitud para ponernos en contacto contigo muy pronto.
                </p>

                <!-- Summary Card -->
                <div style="background:#fff7ed;border-radius:12px;border:1px solid #fed7aa;padding:20px;margin-bottom:24px;">
                    <h3 style="margin:0 0 14px;font-size:14px;text-transform:uppercase;letter-spacing:0.5px;color:#ea580c;font-weight:700;">Detalles de tu solicitud de cumpleaños:</h3>
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size:14px;color:#334155;">
                        @if(!empty($payload['age_to_celebrate']))
                        <tr>
                            <td style="padding:6px 0;font-weight:600;color:#9a3412;width:150px;">Edad a celebrar:</td>
                            <td style="padding:6px 0;color:#0f172a;font-weight:700;">🎉 {{ $payload['age_to_celebrate'] }} años</td>
                        </tr>
                        @endif
                        @if(!empty($payload['event_date']))
                        <tr>
                            <td style="padding:6px 0;font-weight:600;color:#9a3412;">Fecha estimada:</td>
                            <td style="padding:6px 0;color:#ea580c;font-weight:700;">📅 {{ $payload['event_date'] }}</td>
                        </tr>
                        @endif
                        @if(!empty($payload['start_time']))
                        <tr>
                            <td style="padding:6px 0;font-weight:600;color:#9a3412;">Hora:</td>
                            <td style="padding:6px 0;color:#0f172a;">⏰ {{ $payload['start_time'] }}</td>
                        </tr>
                        @endif
                        @if(!empty($payload['program_interest']))
                        <tr>
                            <td style="padding:6px 0;font-weight:600;color:#9a3412;">Programa:</td>
                            <td style="padding:6px 0;color:#0ea5e9;font-weight:600;">{{ strtoupper($payload['program_interest']) }}</td>
                        </tr>
                        @endif
                        @if(!empty($payload['estimated_children']))
                        <tr>
                            <td style="padding:6px 0;font-weight:600;color:#9a3412;">Estimado de niños:</td>
                            <td style="padding:6px 0;color:#0f172a;">{{ $payload['estimated_children'] }} niños</td>
                        </tr>
                        @endif
                    </table>
                </div>

                <div style="background:#f0fdf4;border-radius:12px;padding:16px 20px;border-left:4px solid #22c55e;margin-bottom:24px;">
                    <p style="margin:0;font-size:14px;color:#15803d;line-height:1.5;">
                        ✨ <strong>¿Qué sigue?</strong> Un coordinador de eventos de Little Brands Inc se comunicará contigo vía WhatsApp o llamada telefónica para afinar detalles y confirmar tu reserva.
                    </p>
                </div>

                <p style="margin:0;font-size:15px;line-height:1.6;color:#475569;">
                    ¡Nos vemos muy pronto para celebrar juntos!<br>
                    <strong style="color:#0f172a;">El equipo de Little Brands Inc</strong>
                </p>
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td style="background:#f8fafc;padding:20px 32px;text-align:center;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;line-height:1.5;">
                Little Brands Inc &bull; Todos los derechos reservados.<br>
                Este es un mensaje automático de confirmación.
            </td>
        </tr>
    </table>
</body>
</html>
