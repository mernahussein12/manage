<?php


namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{

//     public function index()
// {
//     $user = auth()->user(); // المستخدم الحالي

//     // جلب التقارير الخاصة بالقسم التابع للمستخدم فقط
//     $reports = Report::whereHas('user', function ($query) use ($user) {
//         $query->where('department_id', $user->department_id);
//     })->with([
//         'user.roles:id,name',  // جلب الأدوار ولكن سيتم استخدام أول واحد فقط
//         'user.department:id,name'
//     ])->get(['id', 'user_id', 'report', 'created_at']);

//     // تعديل البيانات لإعادة ترتيب `name` خارج `user`
//     $formattedReports = $reports->map(function ($report) {
//         return [
//             'id' => $report->id,
//             'user_id' => $report->user_id,
//             'name' => $report->user->name,
//             'report' => $report->report,
//             'created_at' => $report->created_at,
//             'role' => $report->user->roles->first() ? [  // الحصول على أول `role`
//                 'id' => $report->user->roles->first()->id,
//                 'name' => $report->user->roles->first()->name,
//             ] : null,
//             'department' => $report->user->department,
//         ];
//     });

//     return response()->json($formattedReports);
// }

public function index()
{
    $user = auth()->user(); // المستخدم الحالي

    // التأكد من تحميل المستخدم
    if (!$user) {
        return response()->json(['message' => 'المستخدم غير مسجل دخول'], 401);
    }

    // إذا كان المستخدم سوبر أدمن، يجلب كل التقارير
    $reportsQuery = Report::with(['user.roles:id,name', 'user:id,name']);

    if ($user->role !== 'super_admin') {
        // المستخدم العادي يرى فقط التقارير الخاصة بقسمه
        $reportsQuery->whereHas('user', function ($query) use ($user) {
            $query->where('department_id', $user->department_id);
        });
    }

    // تنفيذ الاستعلام
    $reports = $reportsQuery->get(['id', 'user_id', 'report', 'created_at']);

    // **تصحيح المشكلة: طباعة البيانات قبل التنسيق لمعرفة ما إذا كانت فارغة أم لا**
    if ($reports->isEmpty()) {
        return response()->json(['message' => 'لم يتم العثور على تقارير'], 404);
    }

    // إعادة ترتيب البيانات
    $formattedReports = $reports->map(function ($report) {
        return [
            'date' => $report->created_at ? $report->created_at->format('Y-m-d') : null,
            'name_user' => $report->user->name ?? 'غير معروف',
            'role_user' => $report->user->roles->first()->name ?? 'بدون دور',
            'report' => $report->report ?? 'لا يوجد تقرير',
        ];
    });

    return response()->json($formattedReports);
}




    public function store(Request $request)
    {
        $request->validate([
            'report' => 'required|string',
        ]);

        $user = auth()->user(); // المستخدم الحالي

        $report = Report::create([
            'user_id' => $user->id,
            'report' => $request->report,
        ]);

        return response()->json(['message' => 'تم إنشاء التقرير بنجاح', 'report' => $report]);
    }
}
