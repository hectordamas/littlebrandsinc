<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo contacto</title>
</head>
<body style="margin:0;padding:24px;font-family:Arial,sans-serif;background:#f4f8ff;color:#111827;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #dbeafe;border-radius:12px;overflow:hidden;">
        <tr>
            <td style="background:linear-gradient(120deg,#0ea5e9,#22c55e);padding:20px 24px;color:#ffffff;">
                <h1 style="margin:0;font-size:22px;">Little Brands Inc</h1>
                <p style="margin:8px 0 0;font-size:14px;opacity:0.95;">Nuevo mensaje desde la landing page</p>
            </td>
        </tr>
        <tr>
            <td style="padding:24px;">
                <p style="margin:0 0 12px;"><strong>Nombre:</strong> {{ $payload['name'] }}</p>
                <p style="margin:0 0 12px;"><strong>Email:</strong> {{ $payload['email'] }}</p>
                <p style="margin:0 0 12px;"><strong>Telefono:</strong> {{ $payload['phone'] }}</p>
                <p style="margin:0 0 8px;"><strong>Mensaje:</strong></p>
                <div style="padding:14px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;white-space:pre-line;">
                    {{ $payload['message'] }}
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
