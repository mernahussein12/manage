<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\DeveloperRequest;
use App\Http\Resources\DeveloperResource;
use Illuminate\Http\Request;
use App\Models\Developer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Exception;

class DeveloperController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum'); // تأكد من أنك مسجل الدخول
    }


    public function index(Request $request): JsonResponse
    {
        try {
            $query = Developer::query();

            // الأعمدة القابلة للبحث
            $textColumns = ['project_name', 'project_type', 'project_leader', 'support', 'summary'];

            if ($request->filled('search')) {
                $search = trim($request->input('search')); // إزالة المسافات الزائدة

                $query->where(function ($q) use ($search, $textColumns) {
                    foreach ($textColumns as $column) {
                        $q->orWhere($column, 'like', "%{$search}%");
                    }
                });
            }

            $developers = $query->latest()->paginate(10);

            if ($developers->count() === 0) {
                return response()->json(['message' => 'لا توجد نتائج مطابقة'], 404);
            }

            return response()->json([
                'message' => 'تم جلب النتائج بنجاح',
                'data' => DeveloperResource::collection($developers),
                'total' => $developers->total(),
                'current_page' => $developers->currentPage(),
                'last_page' => $developers->lastPage(),
                'per_page' => $developers->perPage(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب البيانات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(DeveloperRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // رفع ملف الـ summary إذا كان موجودًا
            if ($request->hasFile('summary')) {
                $data['summary'] = $request->file('summary')->store('summary', 'public');
            }

            $developer = Developer::create($data);

            $developer->users()->sync($request->team);

            $team = $developer->users()
                ->select('users.id as value', 'users.name as label')
                ->get()
                ->map(function ($user) {
                    return [
                        'value' => $user->value,
                        'label' => $user->label
                    ];
                });

            return response()->json([
                'message' => 'تم إنشاء المشروع بنجاح',
                'data' => new DeveloperResource($developer),
                'team' => $team, // ✅ الآن ستكون فقط `value` و `label`
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء المشروع',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Developer $developer): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'تم جلب المشروع بنجاح',
                'data' => new DeveloperResource($developer)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء جلب المشروع', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Developer $developer): JsonResponse
    {
        try {
            $developer->delete();
            return response()->json(['message' => 'تم حذف المشروع بنجاح'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء حذف المشروع', 'error' => $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        $query = Developer::query();

        if ($request->has('search')) {
            $search = $request->input('search');

            // جلب جميع الأعمدة من جدول المطورين
            $columns = Schema::getColumnListing('developers');

            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%$search%");
                }
            });
        }

        $developers = $query->latest()->paginate(10);

        return DeveloperResource::collection($developers);
    }

    public function filter(Request $request)
    {
        $query = Developer::query();

        if ($request->filled('project_name')) {
            $query->where('project_name', 'like', "%{$request->project_name}%");
        }
        if ($request->filled('project_type')) {
            $query->where('project_type', $request->project_type);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }
        if ($request->filled('project_leader')) {
            $query->where('project_leader', $request->project_leader);
        }
        if ($request->filled('support')) {
            $query->where('support', $request->support);
        }
        if ($request->filled('min_cost') && $request->filled('max_cost')) {
            $query->whereBetween('cost', [$request->min_cost, $request->max_cost]);
        }
        if ($request->filled('min_profit') && $request->filled('max_profit')) {
            $query->whereBetween('profit_margin', [$request->min_profit, $request->max_profit]);
        }

        $developers = $query->paginate(10);

        return response()->json($developers);
    }

    public function getProjectsByDepartment($departmentId)
    {
        $projects = Project::where('department_id', $departmentId)
                           ->where('status', 'pending') // فقط المشاريع التي هي في حالة "pending"
                           ->get();

        if ($projects->isEmpty()) {
            return response()->json(['message' => 'لا توجد مشاريع في هذا القسم'], 404);
        }

        return response()->json($projects, 200);
    }

}
