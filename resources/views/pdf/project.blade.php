<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير المشروع</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; direction: rtl; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: right; }
        th { background: #f8f8f8; }
    </style>
</head>
<body>
    <h2>تفاصيل المشروع</h2>
    <table>
        <tr><th>اسم المشروع</th><td>{{ $project->project_name }}</td></tr>
        <tr><th>نوع المشروع</th><td>{{ $project->project_type }}</td></tr>
        <tr><th>اسم المالك</th><td>{{ $project->owner_name }}</td></tr>
        <tr><th>الدولة</th><td>{{ $project->owner_country }}</td></tr>
        <tr><th>التكلفة</th><td>{{ $project->cost }} $</td></tr>
        <tr><th>الهامش الربحي</th><td>{{ $project->profit_margin }} %</td></tr>
        <tr><th>الدعم الفني</th><td>{{ $project->technical_support ?? 'غير محدد' }}</td></tr>
        <tr><th>التاريخ</th><td>{{ $project->date }}</td></tr>
        <tr><th>الحالة</th><td>{{ $project->status }}</td></tr>
    </table>
</body>
</html>
