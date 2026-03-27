<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Nómina - <?= htmlspecialchars($nomina['nombre_trabajador']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #fff;
            padding: 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 36px;
            padding-bottom: 24px;
            border-bottom: 2px solid #10b981;
        }
        .logo-area h1 {
            font-size: 22px;
            font-weight: 700;
            color: #10b981;
            letter-spacing: -0.5px;
        }
        .logo-area p { color: #64748b; font-size: 12px; margin-top: 2px; }
        .doc-info { text-align: right; }
        .doc-info .badge-nomina {
            display: inline-block;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .doc-info p { color: #64748b; font-size: 11px; line-height: 1.8; }

        .section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #94a3b8;
            margin-bottom: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 28px;
        }
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
        }
        .info-box .label { font-size: 11px; color: #94a3b8; margin-bottom: 3px; }
        .info-box .value { font-size: 13px; font-weight: 600; color: #1e293b; }

        .pay-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .pay-table thead tr {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        .pay-table thead th {
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .pay-table tbody tr:nth-child(even) { background: #f8fafc; }
        .pay-table tbody td {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
        .pay-table tbody td.amount { text-align: right; font-weight: 600; }
        .pay-table tbody td.deduction { color: #ef4444; }
        .pay-table tbody td.benefit   { color: #10b981; }

        .total-row {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        .total-row .label { font-size: 14px; font-weight: 600; color: #15803d; }
        .total-row .amount { font-size: 22px; font-weight: 700; color: #16a34a; }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer .firma-box {
            text-align: center;
            width: 180px;
        }
        .footer .firma-line {
            border-top: 1px solid #94a3b8;
            padding-top: 6px;
            font-size: 11px;
            color: #94a3b8;
        }
        .watermark {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #cbd5e1;
            letter-spacing: 0.5px;
        }

        @media print {
            body { padding: 20px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<!-- Botón de imprimir (oculto en impresión) -->
<div class="no-print" style="text-align:right; margin-bottom:20px;">
    <button onclick="window.print()"
            style="background:linear-gradient(135deg,#10b981,#059669);color:white;border:none;
                   padding:10px 24px;border-radius:20px;font-weight:600;cursor:pointer;font-size:13px;">
        🖨️ Imprimir / Guardar PDF
    </button>
</div>

<!-- HEADER -->
<div class="header">
    <div class="logo-area">
        <h1>🚀 StartLink</h1>
        <p><?= htmlspecialchars($nomina['nombre_empresa'] ?? 'StartLink Cloud') ?></p>
        <p style="margin-top:2px;color:#94a3b8;font-size:11px;">Recibo de Nómina oficial</p>
    </div>
    <div class="doc-info">
        <div class="badge-nomina">Nómina #<?= str_pad($nomina['id'], 6, '0', STR_PAD_LEFT) ?></div>
        <p><strong>Generado:</strong> <?= date('d/m/Y', strtotime($nomina['fecha_generacion'])) ?></p>
        <p><strong>Período:</strong> <?= date('d/m/Y', strtotime($nomina['fecha_inicio_periodo'])) ?> – <?= date('d/m/Y', strtotime($nomina['fecha_fin_periodo'])) ?></p>
    </div>
</div>

<!-- DATOS DEL TRABAJADOR -->
<div class="section-title">Datos del Trabajador</div>
<div class="info-grid">
    <div class="info-box">
        <div class="label">Nombre completo</div>
        <div class="value"><?= htmlspecialchars($nomina['nombre_trabajador']) ?></div>
    </div>
    <div class="info-box">
        <div class="label">Correo electrónico</div>
        <div class="value"><?= htmlspecialchars($nomina['email']) ?></div>
    </div>
    <div class="info-box">
        <div class="label">DNI / Identificación</div>
        <div class="value"><?= htmlspecialchars($nomina['dni'] ?? '—') ?></div>
    </div>
    <div class="info-box">
        <div class="label">Cargo</div>
        <div class="value"><?= htmlspecialchars($nomina['cargo'] ?? '—') ?></div>
    </div>
</div>

<!-- DETALLE DE PAGO -->
<div class="section-title">Detalle de Pago</div>
<table class="pay-table">
    <thead>
        <tr>
            <th>Concepto</th>
            <th>Descripción</th>
            <th style="text-align:right;">Monto</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Horas trabajadas</td>
            <td><?= number_format($nomina['horas_trabajadas'], 1) ?> horas registradas</td>
            <td class="amount benefit">+ $<?= number_format($nomina['salario_bruto'] - $nomina['bonificaciones'], 2) ?></td>
        </tr>
        <?php if ($nomina['bonificaciones'] > 0): ?>
        <tr>
            <td>Bonificaciones</td>
            <td>Bonificaciones del período</td>
            <td class="amount benefit">+ $<?= number_format($nomina['bonificaciones'], 2) ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($nomina['horas_extras'] > 0): ?>
        <tr>
            <td>Horas extras</td>
            <td><?= number_format($nomina['horas_extras'], 1) ?> horas adicionales</td>
            <td class="amount benefit">✓ Incluido</td>
        </tr>
        <?php endif; ?>
        <tr>
            <td>Deducciones</td>
            <td>Retenciones y descuentos</td>
            <td class="amount deduction">- $<?= number_format($nomina['deducciones'], 2) ?></td>
        </tr>
    </tbody>
</table>

<!-- TOTAL NETO -->
<div class="total-row">
    <div class="label">💰 SALARIO NETO A PAGAR</div>
    <div class="amount">$<?= number_format($nomina['salario_neto'], 2) ?></div>
</div>

<!-- FIRMAS -->
<div class="footer">
    <div class="firma-box">
        <div style="height:40px;"></div>
        <div class="firma-line">Firma del Empleado</div>
        <div style="font-size:10px;color:#94a3b8;margin-top:2px;"><?= htmlspecialchars($nomina['nombre_trabajador']) ?></div>
    </div>
    <div style="text-align:center;color:#94a3b8;font-size:10px;">
        <div>Documento generado electrónicamente</div>
        <div><?= date('d/m/Y H:i') ?></div>
    </div>
    <div class="firma-box">
        <div style="height:40px;"></div>
        <div class="firma-line">Firma del Administrador</div>
        <div style="font-size:10px;color:#94a3b8;margin-top:2px;"><?= htmlspecialchars($nomina['nombre_empresa'] ?? 'Empresa') ?></div>
    </div>
</div>

<div class="watermark">
    © <?= date('Y') ?> StartLink Cloud · Este documento tiene validez como comprobante de nómina
</div>

</body>
</html>
