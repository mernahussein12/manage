<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{


    public function index(Request $request): JsonResponse
    {
        try {
            $query = Project::query();

            // تحديد الأعمدة القابلة للبحث
            $textColumns = [
                'employee', 'owner_name', 'owner_number', 'owner_country',
                'project_name', 'project_type', 'price_offer', 'hosting', 'technical_support'
            ];

            // السماح فقط للسوبر أدمن والتيم ليدر برؤية جميع المشاريع
            $user = auth()->user(); // جلب المستخدم الحالي

            if ($user->role !== 'super_admin' && $user->role !== 'team_leader') {
                $query->where('employee', $user->name); // عرض المشاريع الخاصة بالمستخدم فقط
            }

            // تطبيق البحث إذا كان موجودًا
            if ($request->filled('search')) {
                $search = $request->input('search');

                $query->where(function ($q) use ($search, $textColumns) {
                    foreach ($textColumns as $column) {
                        $q->orWhere($column, 'like', "%$search%");
                    }
                });
            }

            $salesProjects = $query->latest()->paginate(10);

            if ($salesProjects->count() === 0) {
                return response()->json(['message' => 'لا توجد نتائج مطابقة'], 404);
            }

            return response()->json([
                'message' => 'تم جلب مشاريع المبيعات بنجاح',
                'data' => ProjectResource::collection($salesProjects),
                'total' => $salesProjects->total(),
                'current_page' => $salesProjects->currentPage(),
                'last_page' => $salesProjects->lastPage(),
                'per_page' => $salesProjects->perPage(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء جلب مشاريع المبيعات', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        // التحقق من صحة البيانات المدخلة
        $data = $request->validate([
            'date' => 'required|date',
            'employee' => 'required|string',
            'owner_name' => 'required|string',
            'owner_number' => 'required|string',
            'owner_country' => 'required|string',
            'project_name' => 'required|string',
            'project_type' => 'required|string',
            // 'price_offer' => 'nullable|file|mimes:pdf|max:5120',
            'price_offer' => 'nullable|file|mimes:pdf|max:10240', // الحد الأقصى 10 ميجابايت
            'cost' => 'required|numeric',
            'initial_payment' => 'required|numeric',
            'profit_margin' => 'required|numeric',
            'hosting' => 'nullable|string',
            'technical_support' => 'nullable|string',
            'department_id' => 'required|exists:departments,id', // القسم الذي سيتم إرسال المشروع إليه
        ]);

        $status = 'pending';  // تعيين حالة المشروع إلى "معلق" عند إضافته

        // التحقق من وجود ملف العرض المقدم (pdf)
        if ($request->hasFile('price_offer')) {
            $file = $request->file('price_offer');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('pdfs'), $filename);
            $data['price_offer'] = 'pdfs/' . $filename;
        }

        // إضافة المشروع إلى قاعدة البيانات
        $project = Project::create($data);

        // تعيين حالة المشروع إلى "pending"
        $project->status = $status;

        // إرسال المشروع إلى القسم المحدد (من خلال department_id)
        // هنا يتم تغيير حالة المشروع إلى "pending" إذا لم تكن قد تم تعيينها مسبقاً
        $project->department_id = $request->department_id;
        $project->save();

        // إعادة المشروع بعد إضافته بنجاح
        return response()->json($project, 201);
    }




    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'date' => 'required|date',
    //         'employee' => 'required|string',
    //         'owner_name' => 'required|string',
    //         'owner_number' => 'required|string',
    //         'owner_country' => 'required|string',
    //         'project_name' => 'required|string',
    //         'project_type' => 'required|string',
    //         // 'price_offer' => 'nullable|file|mimes:pdf|max:5120',
    //         'price_offer' => 'nullable|file|mimes:pdf|max:10240', // الحد الأقصى 10 ميجابايت

    //         'cost' => 'required|numeric',
    //         'initial_payment' => 'required|numeric',
    //         'profit_margin' => 'required|numeric',
    //         'hosting' => 'nullable|string',
    //         'technical_support' => 'nullable|string',
    //         'department_id' => 'required|exists:departments,id',

    //     ]);

    //     $status = 'pending';

    //     if ($request->hasFile('price_offer')) {
    //         $file = $request->file('price_offer');
    //         $filename = time() . '_' . $file->getClientOriginalName();
    //         $file->move(public_path('pdfs'), $filename);
    //         $data['price_offer'] = 'pdfs/' . $filename;
    //     }

    //     $project = Project::create($data);

    //     $project->status = $status;

    //     return response()->json($project, 201);
    // }



    public function show($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'المشروع غير موجود'], 404);
        }

        // تعيين الحالة إلى "pending" إذا لم تكن موجودة
        if (!$project->status) {
            $project->status = 'pending';
        }

        return new ProjectResource($project);
    }


    public function update(Request $request, string $id)
    {
        $project = Project::findOrFail($id);

        $oldStatus = $project->status;
        $newStatus = $request->input('status');

        $project->update($request->all());

        // تسجيل التغيير في السجل
        ProjectHistory::create([
            'project_id' => $project->id,
            'status' => $oldStatus,
            'changed_by' => auth()->user()->name ?? 'System',
        ]);

        return response()->json(['message' => 'تم تحديث المشروع بنجاح', 'project' => $project]);
    }

    public function generateProjectPDF($id)
{
    $user = Auth::user();

    if (!$user || !in_array($user->role, ['super_admin', 'team_lead'])) {
        return response()->json(['message' => 'غير مصرح لك بتنزيل هذا الملف'], 403);
    }

    $project = Project::findOrFail($id);

    $pdf = Pdf::loadView('pdf.project', compact('project'));

    $fileName = "project_{$project->id}.pdf";
    $filePath = public_path($fileName);

    $pdf->save($filePath);

    // إنشاء رابط مباشر للتحميل
    $fileUrl = asset($fileName);

    return response()->json([
        'message' => 'تم إنشاء ملف PDF بنجاح',
        'download_url' => $fileUrl
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getProjectsByDepartment($department_id)
    {
        // Get all projects where the department_id matches the department sent in the request
        $projects = Project::where('department_id', $department_id)
                            ->whereIn('status', ['pending', 'confirmed'])  // Adjust status filter as needed
                            ->get();

        if ($projects->isEmpty()) {
            return response()->json(['message' => 'No projects found for this department.'], 404);
        }

        return response()->json($projects, 200);
    }

    public function updateProjectStatus($projectId, Request $request)
    {
        $project = Project::findOrFail($projectId);

        // التحقق من أن الحالة التي يتم إرسالها هي واحدة من "pending", "accepted", أو "rejected"
        $validatedData = $request->validate([
            'status' => 'required|in:pending,accepted,rejected'
        ]);

        // التحقق من أن هذا القسم هو الذي أرسل المشروع
        if ($project->department_id != $request->department_id) {
            return response()->json(['message' => 'أنت لا تملك صلاحية تعديل هذا المشروع'], 403);
        }

        // تحديث حالة المشروع
        $project->status = $validatedData['status'];
        $project->save();

        return response()->json($project, 200);
    }



public function search(Request $request)
{
    $query = Project::query();

    if ($request->has('search')) {
        $search = $request->input('search');

        $columns = Schema::getColumnListing('projects');

        $query->where(function ($q) use ($search, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%$search%");
            }
        });
    }

    $projects = $query->latest()->paginate(10);

    return ProjectResource::collection($projects);
}
public function filter(Request $request)
{
    $query = Project::query();

    // تطبيق الفلاتر بناءً على القيم المدخلة
    if ($request->filled('date')) {
        $query->whereDate('date', $request->date);
    }
    if ($request->filled('project_type')) {
        $query->where('project_type', $request->project_type);
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('owner_country')) {
        $query->where('owner_country', $request->owner_country);
    }
    if ($request->filled('min_cost') && $request->filled('max_cost')) {
        $query->whereBetween('cost', [$request->min_cost, $request->max_cost]);
    }

    // تنفيذ الاستعلام مع التصفية والإرجاع
    $projects = $query->paginate(10);

    return response()->json($projects);
}

}
