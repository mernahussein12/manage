<?php

namespace App\Http\Controllers;

use App\Models\HostingProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HostingProjectController extends Controller
{
    public function index()
    {
        $projects = HostingProject::all()->map(function ($project) {
            $remainingDays = now()->diffInDays($project->end_date, false); // الفرق بين اليوم و `end_date`

            $project->renewal_required = $remainingDays < 0; // انتهت المدة
            $project->warning = $remainingDays >= 0 && $remainingDays <= 10; // تبقى 10 أيام أو أقل

            return $project->toArray(); // تحويل الكائن إلى مصفوفة لحفظ القيم الجديدة
        });

        return response()->json([
            'status' => true,
            'message' => 'تم جلب المشاريع بنجاح',
            'data' => $projects
        ]);
    }

    public function show($id)
    {
        $project = HostingProject::find($id);

        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => 'المشروع غير موجود'
            ], 404);
        }

        $remainingDays = now()->diffInDays($project->end_date, false);

        // تحقق من حالتي الإنذار والانتهاء
        $project->renewal_required = $remainingDays < 0;
        $project->warning = $remainingDays >= 0 && $remainingDays <= 10;

        return response()->json([
            'status' => true,
            'message' => 'تم جلب بيانات المشروع بنجاح',
            'data' => $project
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'summary' => 'nullable|file|mimes:pdf|max:5120', // السماح فقط بملفات PDF بحجم أقصى 5MB
            'cost' => 'required|numeric',
        ]);

        $data = $request->all();

        if ($request->hasFile('summary')) {
            $data['summary'] = $request->file('summary')->store('summary', 'public');
        }

        $project = HostingProject::create($data);

        return response()->json([
            'status' => true,
            'message' => 'تم إضافة المشروع بنجاح',
            'data' => $project
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $project = HostingProject::find($id);

        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => 'المشروع غير موجود'
            ], 404);
        }

        $request->validate([
            'name_en' => 'sometimes|string',
            'name_ar' => 'sometimes|string',
            'type' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'summary' => 'nullable|file|mimes:pdf|max:5120', // تحديث الملف عند الحاجة
            'cost' => 'sometimes|numeric',
        ]);

        $data = $request->except(['summary']);

        if ($request->hasFile('summary')) {
            // حذف الملف القديم إذا كان موجودًا
            if ($project->summary) {
                Storage::disk('public')->delete($project->summary);
            }
            // حفظ الملف الجديد
            $data['summary'] = $request->file('summary')->store('summary', 'public');
        }

        $project->update($data);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث المشروع بنجاح',
            'data' => $project
        ]);
    }

    public function destroy($id)
    {
        $project = HostingProject::find($id);

        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => 'المشروع غير موجود'
            ], 404);
        }

        $project->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف المشروع بنجاح'
        ]);
    }
}
