<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo contacto - Little Brands Inc</title>
</head>
<body style="margin:0;padding:24px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;background-color:#f4f7fa;color:#1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.05);border:1px solid #e2e8f0;">
        <!-- Header -->
        <tr>
            <td bgcolor="#0ea5e9" style="background-color:#0ea5e9;background-image:linear-gradient(135deg, #0ea5e9 0%, #10b981 100%);padding:28px 32px;text-align:left;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="vertical-align:middle;">
                            <img src="{{ isset($message) && method_exists($message, 'embed') && file_exists(public_path('landing_page/logos/logo-littlebrandsinc.png')) ? $message->embed(public_path('landing_page/logos/logo-littlebrandsinc.png')) : asset('landing_page/logos/logo-littlebrandsinc.png') }}" alt="Little Brands Inc" style="max-height:54px;width:auto;background:#ffffff;padding:6px 12px;border-radius:10px;display:block;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:16px;">
                            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;letter-spacing:-0.5px;">📩 Nuevo Mensaje de Contacto</h1>
                            <p style="margin:4px 0 0;color:#ffffff;font-size:14px;opacity:0.95;">Solicitud recibida desde la página web</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <!-- Content -->
        <tr>
            <td style="padding:32px;">
                <p style="margin:0 0 20px;font-size:15px;line-height:1.5;color:#334155;">
                    Se ha recibido una nueva consulta a través del formulario de contacto. A continuación se detallan los datos:
                </p>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;border-spacing:0;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;padding:8px 16px;">
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;width:140px;font-size:14px;">Representante:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0f172a;font-weight:700;font-size:14px;">{{ $payload['representative_name'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;font-size:14px;">Niño / Niña:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0f172a;font-size:14px;">{{ $payload['child_name'] ?? 'N/A' }} ({{ $payload['child_age'] ?? 'N/A' }} años)</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;font-size:14px;">Programa:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0ea5e9;font-weight:600;font-size:14px;">{{ $payload['program_name'] ?? $payload['program_id'] ?? 'General' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;font-size:14px;">Sede:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0f172a;font-size:14px;">{{ $payload['branch_name'] ?? $payload['branch_id'] ?? 'Todas' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;font-weight:600;color:#475569;font-size:14px;">Correo electrónico:</td>
                        <td style="padding:10px 0;border-bottom:1px solid #edf2f7;color:#0f172a;font-size:14px;"><a href="mailto:{{ $payload['email'] ?? '' }}" style="color:#0ea5e9;text-decoration:none;font-weight:600;">{{ $payload['email'] ?? 'N/A' }}</a></td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;font-weight:600;color:#475569;font-size:14px;">Teléfono:</td>
                        <td style="padding:10px 0;color:#0f172a;font-size:14px;"><a href="tel:{{ $payload['phone'] ?? '' }}" style="color:#0ea5e9;text-decoration:none;font-weight:600;">{{ $payload['phone'] ?? 'N/A' }}</a></td>
                    </tr>
                </table>

                <div style="margin-top:24px;">
                    <p style="margin:0 0 8px;font-size:14px;font-weight:600;color:#475569;">Mensaje / Comentarios:</p>
                    <div style="padding:16px;background:#f1f5f9;border-radius:10px;border-left:4px solid #0ea5e9;color:#334155;font-size:14px;line-height:1.6;white-space:pre-line;">{{ $payload['comment'] ?? 'Sin comentarios adicionales.' }}</div>
                </div>
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td style="background:#f8fafc;padding:16px 32px;text-align:center;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;">
                Little Brands Inc &bull; Notificación interna del sistema
            </td>
        </tr>
    </table>
</body>
</html>
