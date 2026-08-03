<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hemos recibido tu mensaje - Little Brands Inc</title>
</head>
<body style="margin:0;padding:24px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;background-color:#f4f7fa;color:#1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.05);border:1px solid #e2e8f0;">
        <!-- Header -->
        <tr>
            <td bgcolor="#0ea5e9" style="background-color:#0ea5e9;background-image:linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);padding:32px;text-align:center;">
                <img src="{{ isset($message) && method_exists($message, 'embed') && file_exists(public_path('landing_page/logos/logo-littlebrandsinc.png')) ? $message->embed(public_path('landing_page/logos/logo-littlebrandsinc.png')) : asset('landing_page/logos/logo-littlebrandsinc.png') }}" alt="Little Brands Inc" style="max-height:60px;width:auto;background:#ffffff;padding:8px 16px;border-radius:12px;display:inline-block;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                <h1 style="margin:20px 0 0;color:#ffffff;font-size:24px;font-weight:700;letter-spacing:-0.5px;">¡Gracias por escribirnos!</h1>
                <p style="margin:6px 0 0;color:#ffffff;font-size:15px;opacity:0.95;">Hemos recibido tu mensaje correctamente</p>
            </td>
        </tr>
        <!-- Content -->
        <tr>
            <td style="padding:32px;">
                <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#1e293b;">
                    Hola <strong>{{ $payload['representative_name'] ?? 'estimado/a representante' }}</strong>,
                </p>
                <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#475569;">
                    Queremos confirmarte que hemos recibido tu solicitud de información. Nuestro equipo revisará los detalles y te responderá a la brevedad posible.
                </p>

                <!-- Summary Card -->
                <div style="background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;padding:20px;margin-bottom:24px;">
                    <h3 style="margin:0 0 14px;font-size:14px;text-transform:uppercase;letter-spacing:0.5px;color:#0ea5e9;font-weight:700;">Resumen de tu solicitud:</h3>
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size:14px;color:#334155;">
                        @if(!empty($payload['child_name']))
                        <tr>
                            <td style="padding:6px 0;font-weight:600;color:#64748b;width:130px;">Niño/a:</td>
                            <td style="padding:6px 0;color:#0f172a;font-weight:600;">{{ $payload['child_name'] }} ({{ $payload['child_age'] ?? '' }} años)</td>
                        </tr>
                        @endif
                        @if(!empty($payload['program_name']))
                        <tr>
                            <td style="padding:6px 0;font-weight:600;color:#64748b;">Programa:</td>
                            <td style="padding:6px 0;color:#0ea5e9;font-weight:600;">{{ $payload['program_name'] }}</td>
                        </tr>
                        @endif
                        @if(!empty($payload['branch_name']))
                        <tr>
                            <td style="padding:6px 0;font-weight:600;color:#64748b;">Sede de interés:</td>
                            <td style="padding:6px 0;color:#0f172a;">{{ $payload['branch_name'] }}</td>
                        </tr>
                        @endif
                        @if(!empty($payload['phone']))
                        <tr>
                            <td style="padding:6px 0;font-weight:600;color:#64748b;">Teléfono:</td>
                            <td style="padding:6px 0;color:#0f172a;">{{ $payload['phone'] }}</td>
                        </tr>
                        @endif
                    </table>
                </div>

                <div style="background:#f0f9ff;border-radius:12px;padding:16px 20px;border-left:4px solid #0ea5e9;margin-bottom:24px;">
                    <p style="margin:0;font-size:14px;color:#0369a1;line-height:1.5;">
                        💡 <strong>¿Tienes alguna duda urgente?</strong> Puedes contactarnos directamente a través de nuestras vías oficiales de atención.
                    </p>
                </div>

                <p style="margin:0;font-size:15px;line-height:1.6;color:#475569;">
                    Atentamente,<br>
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
