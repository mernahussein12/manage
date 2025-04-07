<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeveloperRequest; // تأكد من استخدام الطلب الصحيح
use App\Http\Resources\MarketingResource;
use App\Models\Marketing;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Schema;

class MarketingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum'); // تأكد من أنك مسجل الدخول
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Marketing::query();

            // تحديد الأعمدة القابلة للبحث
            $textColumns = ['campaign_name', 'campaign_type', 'marketing_leader', 'target_audience', 'description'];

            if ($request->filled('search')) {
                $search = $request->input('search');

                $query->where(function ($q) use ($search, $textColumns) {
                    foreach ($textColumns as $column) {
                        $q->orWhere($column, 'like', "%$search%");
                    }
                });
            }

            $marketingProjects = $query->latest()->paginate(10);

            if ($marketingProjects->count() === 0) {

                return response()->json(['message' => 'لا توجد نتائج مطابقة'], 404);
            }
            return response()->json([
                'message' => 'تم جلب المشاريع التسويقية بنجاح',
                'data' => MarketingResource::collection($marketingProjects),
                'total' => $marketingProjects->total(),
                'current_page' => $marketingProjects->currentPage(),
                'last_page' => $marketingProjects->lastPage(),
                'per_page' => $marketingProjects->perPage(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء جلب المشاريع التسويقية', 'error' => $e->getMessage()], 500);
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

            $marketing = Marketing::create($data);

            // إضافة الفريق عبر الجدول الوسيط
            if ($request->has('team')) {
                $marketing->users()->sync($request->team);
            }

            // جلب المستخدمين الذين ينتمون إلى المشروع بصيغة label, value فقط
            $team = $marketing->users()
                ->select('users.id as value', 'users.name as label') // ✅ تحديد الأعمدة المطلوبة فقط
                ->get()
                ->map(function ($user) {
                    return [
                        'value' => $user->value,
                        'label' => $user->label
                    ]; // ✅ تأكيد إزالة أي بيانات إضافية مثل `roles`
                });

            return response()->json([
                'message' => 'تم إنشاء المشروع التسويقي بنجاح',
                'data' => new MarketingResource($marketing),
                'team' => $team, // ✅ الآن ستكون فقط `value` و `label`
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء المشروع التسويقي',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Marketing $marketing): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'تم جلب المشروع التسويقي بنجاح',
                'data' => new MarketingResource($marketing)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء جلب المشروع التسويقي', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(DeveloperRequest $request, Marketing $marketing): JsonResponse
    {
        try {
            $data = $request->validated();

            // رفع ملف الـ summary إذا كان موجودًا
            if ($request->hasFile('summary')) {
                $data['summary'] = $request->file('summary')->store('summary', 'public');
            }

            $marketing->update($data);

            // تحديث الفريق
            if ($request->has('team')) {
                $marketing->users()->sync($request->team);
            }

            return response()->json([
                'message' => 'تم تحديث المشروع التسويقي بنجاح',
                'data' => new MarketingResource($marketing)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء تحديث المشروع التسويقي', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Marketing $marketing): JsonResponse
    {
        try {
            $marketing->delete();

            return response()->json(['message' => 'تم حذف المشروع التسويقي بنجاح'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء حذف المشروع التسويقي', 'error' => $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        $query = Marketing::query();

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

        $markets = $query->latest()->paginate(10);

        return MarketingResource::collection($markets);
    }

    public function filterMarket(Request $request): JsonResponse
    {
        try {
            $query = Marketing::query();

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

            // ✅ فلترة الكلفة (cost) بشكل صحيح
            if ($request->filled('min_cost')) {
                $query->where('cost', '>=', $request->min_cost);
            }
            if ($request->filled('max_cost')) {
                $query->where('cost', '<=', $request->max_cost);
            }

            // ✅ فلترة هامش الربح (profit_margin) بشكل صحيح
            if ($request->filled('min_profit')) {
                $query->where('profit_margin', '>=', $request->min_profit);
            }
            if ($request->filled('max_profit')) {
                $query->where('profit_margin', '<=', $request->max_profit);
            }

            $marketingProjects = $query->paginate(10);

            if ($marketingProjects->isEmpty()) {
                return response()->json(['message' => 'لا توجد نتائج مطابقة'], 404);
            }

            return response()->json([
                'message' => 'تم جلب المشاريع التسويقية بنجاح',
                'data' => MarketingResource::collection($marketingProjects)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء البحث', 'error' => $e->getMessage()], 500);
        }
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
