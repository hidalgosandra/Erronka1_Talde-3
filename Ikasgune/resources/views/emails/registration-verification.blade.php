<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:32px 16px;background:#f7f8f2;color:#192b37;font-family:Arial,sans-serif;">
    <div style="max-width:520px;margin:auto;padding:32px;background:#ffffff;border:1px solid #dce5e0;border-radius:20px;">
        <p style="margin:0 0 24px;color:#2463a0;font-size:24px;font-weight:bold;">eskolak.</p>
        <h1 style="font-size:24px;line-height:1.3;">{{ __('Verifica tu cuenta de Eskolak') }}</h1>
        <p style="line-height:1.6;">{{ __('Introduce este código de 6 dígitos para completar tu registro:') }}</p>
        <p style="margin:24px 0;padding:20px;background:#edf5f1;color:#2463a0;text-align:center;font-family:monospace;font-size:32px;font-weight:bold;letter-spacing:8px;">{{ $code }}</p>
        <p style="line-height:1.6;">{{ __('El código caduca en 10 minutos. Si no has solicitado este registro, puedes ignorar este mensaje.') }}</p>
        <p style="color:#53636b;font-size:14px;line-height:1.6;">{{ __('Vuelve a la ventana donde te has registrado para introducirlo. No compartas este código.') }}</p>
    </div>
</body>
</html>
