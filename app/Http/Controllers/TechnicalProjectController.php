<?php

namespace App\Http\Controllers;

use App\Models\TechnicalProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TechnicalProjectController extends Controller
{

    public function index()
    {
        $projects = TechnicalProject::all()->map(function ($project) {
            $remainingDays = now()->diffInDays($project->end_date, false);

            $project->renewal_required = $remainingDays < 0;
            $project->warning = $remainingDays >= 0 && $remainingDays <= 10;
            return $project->toArray();
        });

        return response()->json([
            'status' => true,
            'message' => 'تم جلب المشاريع بنجاح',
            'data' => $projects
        ]);
    }

    public function show($id)
    {
        $project = TechnicalProject::find($id);

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

        $project = TechnicalProject::create($data);

        return response()->json([
            'status' => true,
            'message' => 'تم إضافة المشروع بنجاح',
            'data' => $project
        ], 201);
    }

    public function update(Request $request, $id)
    {

        $project = TechnicalProject::find($id);

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
            'summary' => 'nullable|file|mimes:pdf|max:5120',
            'cost' => 'sometimes|numeric',
        ]);

        // التحقق من البيانات الجديدة
        $data = collect($request->except('summary'))->filter()->toArray();
        if (empty($data) && !$request->hasFile('summary')) {
            return response()->json([
                'status' => false,
                'message' => 'لم يتم إرسال أي بيانات جديدة'
            ], 400);
        }

        // تحديث البيانات النصية
        $project->fill($data);

        // التعامل مع ملف PDF الجديد
        if ($request->hasFile('summary')) {
            if ($project->summary) {
                Storage::disk('public')->delete($project->summary);
            }
            $project->summary = $request->file('summary')->store('summary', 'public');
        }

        // حفظ التعديلات
        $saved = $project->save();
        $project->touch(); // فرض تحديث قاعدة البيانات

        if (!$saved) {
            return response()->json([
                'status' => false,
                'message' => 'فشل في تحديث المشروع'
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث المشروع بنجاح',
            'data' => $project->fresh()
        ], 200);
    }

    public function destroy($id)
    {
        $project = TechnicalProject::find($id);

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
