<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Expense;
use Illuminate\Http\Request;
use App\Http\Resources\FinanceResource;
use App\Models\AccountsProject;
use App\Models\Expenses;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * عرض التقرير المالي لكل شهر
     */
    public function index()
    {
        $this->authorizeSuperAdmin();

        // جلب جميع المشاريع وتجميع بياناتها حسب الشهر والسنة
        $projects = DB::table('accounts_projects')
            ->select(
                'month',
                'year',
                'name', // اسم المشروع ليظهر في التفاصيل
                'cost',
                DB::raw('(cost * 0.05) as project_percentage') // حساب نسبة 5% لكل مشروع
            )
            ->get();

        // حساب إجمالي الإيرادات لكل شهر
        $months = DB::table('accounts_projects')
            ->select(
                'month',
                'year',
                DB::raw('SUM(cost) as total_revenue')
            )
            ->groupBy('month', 'year');

        // دمج الإيرادات مع المصروفات واحتساب صافي الربح
        $results = DB::table('expenses')
            ->rightJoinSub($months, 'months', function ($join) {
                $join->on('expenses.month', '=', 'months.month')
                    ->on('expenses.year', '=', 'months.year');
            })
            ->select(
                'months.month',
                'months.year',
                DB::raw('COALESCE(SUM(months.total_revenue), 0) as total_revenue'),
                DB::raw('COALESCE(SUM(expenses.amount), 0) as total_expenses'),
                DB::raw('(COALESCE(SUM(months.total_revenue), 0)) - COALESCE(SUM(expenses.amount), 0) as net_profit')
            )
            ->groupBy('months.month', 'months.year')
            ->get();

        // ربط المشاريع مع بيانات الأرباح الشهرية
        $formattedResults = $results->map(function ($monthData) use ($projects) {
            // جلب المشاريع الخاصة بنفس الشهر
            $monthProjects = $projects->where('month', $monthData->month)
                ->where('year', $monthData->year)
                ->map(function ($project) {
                    return [
                        'name' => $project->name,
                        'cost' => $project->cost,
                        'project_percentage' => $project->project_percentage
                    ];
                })
                ->values(); // إعادة ترتيب الفهرس

            return [
                'month' => $monthData->month,
                'year' => $monthData->year,
                'total_revenue' => $monthData->total_revenue,
                'total_expenses' => $monthData->total_expenses,
                'net_profit' => $monthData->net_profit,
                'projects' => $monthProjects, // إضافة تفاصيل المشاريع
            ];
        });

        return response()->json(['data' => $formattedResults]);
    }





    /**
     * إنشاء مشروع جديد - فقط للسوبر أدمن
     */
    public function storeProject(Request $request)
    {
        $this->authorizeSuperAdmin(); // تأكد من أن المستخدم هو سوبر أدمن

        $request->validate([
            'name' => 'required|string',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'cost' => 'required|numeric|min:0'
        ]);

        $project = AccountsProject::create($request->all());

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project
        ], 201);
    }

    /**
     * إضافة مصروف جديد - فقط للسوبر أدمن
     */
    public function storeExpense(Request $request)
    {
        $this->authorizeSuperAdmin(); // تأكد من أن المستخدم هو سوبر أدمن

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'amount' => 'required|numeric|min:0'
        ]);

        $expense = Expenses::create($request->all());

        return response()->json([
            'message' => 'Expense added successfully',
            'expense' => $expense
        ], 201);
    }

    /**
     * التحقق مما إذا كان المستخدم سوبر أدمن
     */
    private function authorizeSuperAdmin()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized. Only Super Admin can perform this action.');
        }
    }
}
