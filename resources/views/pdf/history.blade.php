<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير سجل الطلبات</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; direction: rtl; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 10px; text-align: right; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>تقرير سجل الطلبات</h2>
    <table>
        <thead>
            <tr>
                <th>رقم السجل</th>
                <th>رقم الطلب</th>
                <th>المستخدم</th>
                <th>الإجراء</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history as $record)
            <tr>
                <td>{{ $record->id }}</td>
                <td>{{ $record->request_id }}</td>
                <td>{{ $record->user->name ?? 'غير معروف' }}</td>
                <td>{{ $record->action }}</td>
                <td>{{ $record->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
